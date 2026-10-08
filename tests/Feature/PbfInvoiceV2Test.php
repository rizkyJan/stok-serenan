<?php

use App\Models\{Product, ProductBatch, PurchaseInvoice, Supplier, User};
use App\Services\InvoiceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('menghitung diskon per barang dan pajak nilai lain secara konsisten', function () {
    $result = app(InvoiceCalculator::class)->calculate([
        'discount' => 9000, 'shipping' => 3000, 'tax_mode' => 'nilai_lain', 'tax_rate' => 12,
        'items' => [
            ['purchase_quantity' => 2, 'unit_multiplier' => 100, 'purchase_unit_cost' => 50000,
             'line_discount_percent' => 10, 'line_discount_amount' => 1000],
            ['purchase_quantity' => 1, 'unit_multiplier' => 30, 'purchase_unit_cost' => 30000,
             'line_discount_percent' => 0, 'line_discount_amount' => 0],
        ],
    ]);
    expect($result['subtotal'])->toBe(130000.0)
        ->and($result['line_discount_total'])->toBe(11000.0)
        ->and($result['dpp'])->toBe(110000.0)
        ->and($result['other_dpp'])->toBe(100833.33)
        ->and($result['tax'])->toBe(12100.0)
        ->and($result['total'])->toBe(125100.0);
});

it('menyimpan faktur kompleks dan mempertahankan penambahan stok yang benar', function () {
    Storage::fake('pbf_private');
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $pbf = Supplier::create(['name' => 'PBF Testing', 'npwp' => '00.000.000.0-000.000']);
    $product = Product::create(['sku' => 'INV2-001', 'name' => 'Paracetamol Uji', 'unit' => 'tablet']);
    $payload = [
        'supplier_id' => $pbf->id, 'invoice_no' => 'INV-NEW-001',
        'invoice_date' => '2026-10-08', 'received_date' => '2026-10-08', 'due_date' => '2026-11-07',
        'recipient_name' => 'Apotek Serenan', 'document_total' => 103000,
        'discount' => 0, 'tax_mode' => 'manual', 'tax' => 3000, 'shipping' => 0,
        'items' => [[
            'product_id' => $product->id, 'purchase_quantity' => 2, 'unit_multiplier' => 100,
            'purchase_unit' => 'box', 'purchase_unit_cost' => 50000, 'batch_number' => 'BATCH-V2',
            'expires_at' => '2028-01-01', 'zb_code' => '3',
            'line_discount_percent' => 0, 'line_discount_amount' => 0,
        ]],
        'attachment' => UploadedFile::fake()->create('faktur-asli.pdf', 100, 'application/pdf'),
    ];
    $this->actingAs($admin)->post(route('purchases.store'), $payload)->assertRedirect();
    $invoice = PurchaseInvoice::firstOrFail();
    expect((float) $invoice->total)->toBe(103000.0)
        ->and((float) $invoice->document_total)->toBe(103000.0)
        ->and($invoice->items()->first()->zb_code)->toBe('3')
        ->and(ProductBatch::sum('quantity_available'))->toBe(200);
    Storage::disk('pbf_private')->assertExists($invoice->attachment_path);
    $this->get(route('purchases.print', $invoice))->assertOk()->assertSee('SALINAN FAKTUR');
    $this->get(route('purchases.attachment', $invoice))->assertOk();
});

it('menolak faktur bila nominal dokumen tidak sesuai dengan perhitungan', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $pbf = Supplier::create(['name' => 'PBF Selisih']);
    $product = Product::create(['sku' => 'INV2-002', 'name' => 'Masker Uji', 'unit' => 'pcs']);
    $this->actingAs($admin)->post(route('purchases.store'), [
        'supplier_id' => $pbf->id, 'invoice_no' => 'INV-NEW-002',
        'invoice_date' => '2026-10-08', 'received_date' => '2026-10-08', 'due_date' => '2026-11-07',
        'document_total' => 200000, 'discount' => 0, 'tax_mode' => 'none', 'shipping' => 0,
        'items' => [[ 'product_id' => $product->id, 'purchase_quantity' => 1,
            'unit_multiplier' => 10, 'purchase_unit' => 'box', 'purchase_unit_cost' => 100000,
            'batch_number' => 'BATCH-TEST', ]],
    ])->assertSessionHasErrors('document_total');
    expect(PurchaseInvoice::count())->toBe(0)
        ->and(ProductBatch::count())->toBe(0);
});

it('melindungi unduhan lampiran dari akses tanpa login', function () {
    $this->get('/barang-masuk/1/lampiran')->assertRedirect('/login');
    $this->get('/barang-masuk/1/cetak')->assertRedirect('/login');
});
