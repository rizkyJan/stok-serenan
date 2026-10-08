<?php

namespace App\Http\Controllers;

use App\Models\{Supplier, Product, PurchaseInvoice, ProductBatch, StockMovement};
use App\Services\InvoiceCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $q = PurchaseInvoice::with('supplier');
        if ($s = trim((string) $request->query('q', ''))) {
            $q->where(fn ($b) => $b->where('invoice_no', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn ($b) => $b->where('name', 'like', "%{$s}%")));
        }
        return view('purchases.index', ['invoices' => $q->latest()->paginate(15)->withQueryString()]);
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, InvoiceCalculator $calculator)
    {
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('is_active', true)],
            'invoice_no' => 'required|string|max:100',
            'invoice_date' => 'required|date', 'received_date' => 'required|date',
            'due_date' => 'required|date',
            'recipient_name' => 'nullable|string|max:255',
            'document_total' => 'nullable|numeric|min:0|max:999999999999',
            'discount' => 'required|numeric|min:0|max:999999999999',
            'tax_mode' => ['nullable', Rule::in(['none', 'standard', 'nilai_lain', 'manual'])],
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax' => 'required_if:tax_mode,manual|nullable|numeric|min:0|max:999999999999',
            'shipping' => 'required|numeric|min:0|max:999999999999',
            'notes' => 'nullable|string|max:3000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:8192',
            'items' => 'required|array|min:1|max:50',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('is_active', true)],
            'items.*.purchase_quantity' => 'required|integer|min:1|max:1000000',
            'items.*.unit_multiplier' => 'required|integer|min:1|max:1000000',
            'items.*.purchase_unit' => 'required|string|max:50',
            'items.*.purchase_unit_cost' => 'required|numeric|min:0|max:9999999999',
            'items.*.zb_code' => 'nullable|string|max:30',
            'items.*.line_discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.line_discount_amount' => 'nullable|numeric|min:0|max:999999999999',
            'items.*.batch_number' => 'required|string|max:100',
            'items.*.expires_at' => 'nullable|date',
        ]);
        if (PurchaseInvoice::where('supplier_id', $data['supplier_id'])->where('invoice_no', $data['invoice_no'])->exists()) {
            throw ValidationException::withMessages(['invoice_no' => 'Nomor faktur sudah tercatat untuk PBF tersebut.']);
        }

        $amounts = $calculator->calculate($data);
        if (isset($data['document_total']) && abs(round((float) $data['document_total'], 2) - $amounts['total']) > 0.009) {
            throw ValidationException::withMessages([
                'document_total' => 'Total perhitungan (Rp '.number_format($amounts['total'], 2, ',', '.').') belum sesuai dengan total di faktur asli (Rp '.number_format($data['document_total'], 2, ',', '.').'). Periksa diskon dan PPN.',
            ]);
        }
        $attachment = $request->file('attachment');
        $path = null;
        try {
            // Private storage: lampiran hanya dapat dibuka melalui endpoint ber-login.
            if ($attachment) {
                $path = $attachment->store('pbf-invoices', 'pbf_private');
                if ($path === false) {
                    throw ValidationException::withMessages(['attachment' => 'Gagal menyimpan lampiran.']);
                }
            }
            $invoice = DB::transaction(function () use ($data, $amounts, $attachment, $path) {
                $invoice = PurchaseInvoice::create([
                    'supplier_id' => $data['supplier_id'], 'invoice_no' => $data['invoice_no'],
                    'invoice_date' => $data['invoice_date'], 'received_date' => $data['received_date'],
                    'due_date' => $data['due_date'],
                    'document_total' => $data['document_total'] ?? null, 'recipient_name' => $data['recipient_name'] ?? 'Apotek Serenan',
                    'subtotal' => $amounts['subtotal'], 'line_discount_total' => $amounts['line_discount_total'],
                    'net_items_total' => $amounts['net_items_total'], 'discount' => $amounts['discount'],
                    'dpp' => $amounts['dpp'], 'other_dpp' => $amounts['other_dpp'],
                    'tax_mode' => $amounts['tax_mode'], 'tax_rate' => $amounts['tax_rate'],
                    'tax' => $amounts['tax'], 'shipping' => $amounts['shipping'],
                    'total' => $amounts['total'], 'paid_amount' => 0,
                    'notes' => $data['notes'] ?? null, 'created_by' => auth()->id(),
                    'attachment_path' => $path,
                    'attachment_filename' => ($attachment ? substr(basename($attachment->getClientOriginalName()), 0, 240) : null),
                    'attachment_mime' => $attachment?->getMimeType(),
                ]);
                foreach ($amounts['lines'] as $line) {
                    $item = $line['item'];
                    $qty = $line['baseQty'];
                    $batch = ProductBatch::create([
                        'product_id' => $item['product_id'], 'batch_number' => $item['batch_number'],
                        'expires_at' => $item['expires_at'] ?? null, 'quantity_received' => $qty,
                        'quantity_available' => $qty,
                        // Nilai per unit untuk valuasi stok dibagi setelah diskon item dan alokasi diskon faktur proporsional.
                        'unit_cost' => round(
                            ($line['net'] - ($amounts['net_items_total'] > 0
                                ? $amounts['discount'] * $line['net'] / $amounts['net_items_total'] : 0)) / $qty,
                            4
                        ),
                    ]);
                    $invoice->items()->create([
                        'product_id' => $item['product_id'], 'product_batch_id' => $batch->id,
                        'purchase_quantity' => $item['purchase_quantity'], 'purchase_unit' => $item['purchase_unit'],
                        'unit_multiplier' => $item['unit_multiplier'], 'quantity_base' => $qty,
                        'purchase_unit_cost' => $item['purchase_unit_cost'], 'zb_code' => $item['zb_code'] ?? null,
                        'line_gross' => $line['gross'], 'line_discount_percent' => $line['percent'],
                        'line_discount_amount' => $line['additional'], 'line_discount_total' => $line['discount'],
                        'line_total' => $line['net'],
                    ]);
                    StockMovement::create([
                        'product_id' => $item['product_id'], 'product_batch_id' => $batch->id,
                        'type' => 'in', 'quantity_change' => $qty,
                        'reference_type' => 'purchase_invoice', 'reference_id' => $invoice->id,
                        'notes' => 'Penerimaan faktur '.$invoice->invoice_no,
                        'created_by' => auth()->id(),
                    ]);
                }
                return $invoice;
            });
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('pbf_private')->delete($path);
            }
            throw $e;
        }
        return redirect()->route('purchases.show', $invoice)
            ->with('success', 'Faktur dan rincian diskon/PPN tersimpan. Stok otomatis bertambah.');
    }

    public function show(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'creator', 'items.product', 'items.batch', 'payments']);
        return view('purchases.show', compact('invoice'));
    }

    public function print(PurchaseInvoice $invoice)
    {
        $invoice->load(['supplier', 'creator', 'items.product', 'items.batch', 'payments']);
        return view('purchases.print', compact('invoice'));
    }

    public function attachment(PurchaseInvoice $invoice)
    {
        abort_unless($invoice->attachment_path && Storage::disk('pbf_private')->exists($invoice->attachment_path), 404);
        $filename = $invoice->attachment_filename ?: 'faktur-asli';
        return Storage::disk('pbf_private')->download($invoice->attachment_path, $filename);
    }

    public function uploadAttachment(Request $request, PurchaseInvoice $invoice)
    {
        $request->validate(['attachment' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:8192']);
        $file = $request->file('attachment');
        $path = $file->store('pbf-invoices', 'pbf_private');
        if ($path === false) {
            throw ValidationException::withMessages(['attachment' => 'Lampiran tidak berhasil disimpan.']);
        }
        $oldPath = $invoice->attachment_path;
        try {
            $invoice->update([
                'attachment_path' => $path,
                'attachment_filename' => substr(basename($file->getClientOriginalName()), 0, 240),
                'attachment_mime' => $file->getMimeType(),
            ]);
        } catch (Throwable $e) {
            Storage::disk('pbf_private')->delete($path);
            throw $e;
        }
        if ($oldPath) {
            Storage::disk('pbf_private')->delete($oldPath);
        }
        return back()->with('success', 'Lampiran faktur berhasil diperbarui.');
    }
}
