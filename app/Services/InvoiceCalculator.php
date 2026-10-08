<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

/**
 * Perhitungan operasional berdasarkan angka di dokumen pemasok.
 * Bukan mesin penerbit faktur pajak atau penentu kewajiban PPN.
 */
class InvoiceCalculator
{
    public function calculate(array $data): array
    {
        $lines = [];
        $grossTotal = 0.0;
        $itemDiscountTotal = 0.0;

        foreach ($data['items'] as $position => $item) {
            $baseQty = (int) $item['purchase_quantity'] * (int) $item['unit_multiplier'];
            if ($baseQty > 100000000) {
                throw ValidationException::withMessages(['items' => 'Konversi stok maksimal 100 juta satuan per baris.']);
            }

            $gross = round((int) $item['purchase_quantity'] * (float) $item['purchase_unit_cost'], 2);
            if ($gross > 999999999999) {
                throw ValidationException::withMessages(['items' => 'Harga bruto salah satu barang terlalu besar.']);
            }
            $percent = (float) ($item['line_discount_percent'] ?? 0);
            $additional = round((float) ($item['line_discount_amount'] ?? 0), 2);
            $percentDiscount = round($gross * $percent / 100, 2);
            $discount = round($percentDiscount + $additional, 2);
            if ($discount > $gross) {
                throw ValidationException::withMessages([
                    "items.$position.line_discount_amount" => 'Jumlah potongan di barang '.($position + 1).' melebihi harga kotor barang.',
                ]);
            }
            $net = round($gross - $discount, 2);
            $grossTotal = round($grossTotal + $gross, 2);
            $itemDiscountTotal = round($itemDiscountTotal + $discount, 2);
            $lines[] = compact('item', 'baseQty', 'gross', 'percent', 'additional', 'discount', 'net');
        }

        if ($grossTotal > 999999999999) {
            throw ValidationException::withMessages(['items' => 'Total bruto faktur melebihi batas yang diperbolehkan.']);
        }
        $netItems = round($grossTotal - $itemDiscountTotal, 2);
        $invoiceDiscount = round((float) ($data['discount'] ?? 0), 2);
        if ($invoiceDiscount > $netItems) {
            throw ValidationException::withMessages(['discount' => 'Diskon faktur tidak boleh melebihi jumlah bersih seluruh barang.']);
        }
        $dpp = round($netItems - $invoiceDiscount, 2);
        $mode = $data['tax_mode'] ?? 'manual';
        $rate = (float) ($data['tax_rate'] ?? 12);
        $otherDpp = $mode === 'nilai_lain' ? round($dpp * 11 / 12, 2) : 0.0;
        $tax = match ($mode) {
            'none' => 0.0,
            'standard' => round($dpp * $rate / 100, 2),
            'nilai_lain' => round($otherDpp * $rate / 100, 2),
            default => round((float) ($data['tax'] ?? 0), 2),
        };
        $shipping = round((float) ($data['shipping'] ?? 0), 2);
        $total = round($dpp + $tax + $shipping, 2);
        if ($total > 999999999999 || $total < 0) {
            throw ValidationException::withMessages(['tax' => 'Total faktur di luar batas yang diperbolehkan.']);
        }

        return [
            'lines' => $lines, 'subtotal' => $grossTotal, 'line_discount_total' => $itemDiscountTotal,
            'net_items_total' => $netItems, 'discount' => $invoiceDiscount,
            'dpp' => $dpp, 'other_dpp' => $otherDpp, 'tax_mode' => $mode,
            'tax_rate' => $mode === 'manual' || $mode === 'none' ? 0 : $rate,
            'tax' => $tax, 'shipping' => $shipping, 'total' => $total,
        ];
    }
}
