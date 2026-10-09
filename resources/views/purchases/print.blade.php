<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Salinan Faktur {{$invoice->invoice_no}} - Apotek Serenan</title>
<style>
@page{size:A4 landscape;margin:11mm 12mm}
*{box-sizing:border-box}body{font-family:Arial,Helvetica,sans-serif;color:#172e29;background:#edf3f0;margin:0;font-size:11px}
.print-bar{max-width:1120px;margin:20px auto;display:flex;justify-content:space-between;align-items:center;gap:15px;background:#fff;border:1px solid #dce7e2;padding:13px 18px;border-radius:10px}.print-bar button{background:#08745c;border:0;color:#fff;padding:10px 17px;cursor:pointer;border-radius:7px;font-weight:bold}.print-bar a{color:#0a7158;text-decoration:none}.sheet{width:277mm;max-width:100%;background:white;margin:15px auto;padding:13mm 10mm;min-height:175mm;box-shadow:0 8px 30px #203e3122}.sheet-head{display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:15px;border-bottom:3px solid #164b3f}.heading-brand{font-size:19px;font-weight:900;letter-spacing:1.5px}.heading-muted{font-size:10px;color:#5b796e;margin-top:6px}.copy-flag{font-size:10px;color:#8c6421;background:#fff4dd;border:1px solid #f1d4a3;padding:7px 10px;display:inline-block;border-radius:4px}.document-title{font-size:21px;letter-spacing:2px;font-weight:900;margin:0 0 9px}.header-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px;margin:16px 0 20px}.header-card{border:1px solid #d9e6df;padding:11px 13px}.header-card h2{font-size:10px;text-transform:uppercase;color:#647c73;letter-spacing:.7px;margin:0 0 10px}.header-card p{margin:3px 0}.meta-row{display:flex;justify-content:space-between;gap:10px;margin:4px 0}.meta-row span{color:#6e827a}.meta-row strong{text-align:right}.invoice-table{border-collapse:collapse;width:100%;font-size:9px}th,td{padding:8px 6px;border:1px solid #bed0c5;vertical-align:top}th{background:#e8f3ed;font-size:8px;text-align:center;text-transform:uppercase;letter-spacing:.35px}td.num{text-align:right;white-space:nowrap}td.center{text-align:center;white-space:nowrap}.summary-grid{display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start;margin-top:14px}.summary-notes{color:#577065;line-height:1.65}.summary-notes h3{font-size:11px;color:#2f5c4e;margin:0 0 8px}.money-table{width:100%;border-collapse:collapse;font-size:10px}.money-table td{border:0;border-bottom:1px solid #e3ebe6;padding:7px 6px}.money-table td:last-child{text-align:right;font-weight:bold;white-space:nowrap}.money-table tr.total{background:#e4f2ea;color:#124c3c;font-size:12px;font-weight:bold}.footer{margin-top:27px;border-top:1px solid #dbe6df;padding-top:11px;color:#6a8277;font-size:9px;display:flex;justify-content:space-between;gap:12px}.print-note{color:#7d5436;background:#fff7ec;padding:8px 10px;margin-top:8px}
@media print{body{background:#fff}.print-bar{display:none}.sheet{width:auto;max-width:none;padding:0;margin:0;box-shadow:none;min-height:0}.header-grid,.summary-grid,.sheet-head{break-inside:avoid}thead{display:table-header-group}tr{break-inside:avoid}.copy-flag,.invoice-table th,.money-table tr.total{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
@media(max-width:700px){.print-bar{margin:0;border-radius:0}.sheet{margin:0;padding:20px;overflow:auto}.summary-grid,.header-grid{grid-template-columns:1fr}.invoice-table{min-width:800px}}
</style>
</head>
<body>
<div class="print-bar"><div><strong>Pratinjau cetak faktur</strong><p style="margin:5px 0 0;color:#75867d">Tekan Cetak, lalu pilih <b>Save as PDF</b> pada browser.</p></div><div><a href="{{route('purchases.show',$invoice)}}">← Detail Faktur</a> &nbsp; <button onclick="window.print()">⎙ Cetak / Simpan PDF</button></div></div>
<main class="sheet">
  <div class="sheet-head">
    <div><div class="heading-brand">✚ APOTEK SERENAN</div><div class="heading-muted">Sistem Inventaris · Arsip Pembelian Barang Masuk</div></div>
    <div style="text-align:right"><div class="document-title">SALINAN FAKTUR</div><div class="copy-flag">DOKUMEN INTERNAL — BUKAN FAKTUR PAJAK RESMI</div></div>
  </div>
  <div class="header-grid">
    <div class="header-card"><h2>Pemasok / PBF</h2><p><strong>{{$invoice->supplier->name}}</strong></p><p>{{$invoice->supplier->address ?: 'Alamat PBF tidak diisi'}}</p>@if($invoice->supplier->npwp)<p>NPWP: {{$invoice->supplier->npwp}}</p>@endif<p>{{$invoice->supplier->phone}}</p></div>
    <div class="header-card"><h2>Data penerimaan</h2>
      <div class="meta-row"><span>Nomor faktur</span><strong>{{$invoice->invoice_no}}</strong></div>
      <div class="meta-row"><span>Petugas penerima</span><strong>{{$invoice->recipient_name ?: 'Apotek Serenan'}}</strong></div>
      <div class="meta-row"><span>Tanggal faktur</span><strong>{{$invoice->invoice_date->format('d/m/Y')}}</strong></div>
      <div class="meta-row"><span>Tanggal diterima</span><strong>{{$invoice->received_date->format('d/m/Y')}}</strong></div>
      <div class="meta-row"><span>Jatuh tempo pembayaran</span><strong>{{$invoice->due_date->format('d/m/Y')}}</strong></div>
    </div>
  </div>
  <table class="invoice-table">
    <thead><tr><th>No.</th><th>ZB</th><th>Unit</th><th>Qty</th><th>Nama barang / produk</th><th>No. Batch</th><th>ED</th><th>Harga satuan</th><th>Pot. %</th><th>Potongan total</th><th>Jumlah netto</th></tr></thead>
    <tbody>
    @foreach($invoice->items as $item)
      <tr>
        <td class="center">{{$loop->iteration}}</td><td class="center">{{$item->zb_code ?: '—'}}</td>
        <td class="center">{{$item->purchase_unit}}</td><td class="num">{{$item->purchase_quantity}}</td>
        <td><strong>{{$item->product->name}}</strong><div style="font-size:8px;color:#70877c">{{$item->product->sku}} · Masuk {{$item->quantity_base}} {{$item->product->unit}}</div></td>
        <td>{{$item->batch->batch_number}}</td><td class="center">{{$item->batch->expires_at?->format('m/y') ?: '—'}}</td>
        <td class="num">{{number_format($item->purchase_unit_cost,2,',','.')}}</td>
        <td class="num">{{number_format($item->line_discount_percent,2,',','.')}}%</td>
        <td class="num">{{number_format($item->line_discount_total,2,',','.')}}</td>
        <td class="num"><strong>{{number_format($item->line_total,2,',','.')}}</strong></td>
      </tr>
    @endforeach
    </tbody>
  </table>
  <div class="summary-grid">
    <div class="summary-notes"><h3>Informasi Tambahan</h3>
      <div>Pencatatan oleh: {{$invoice->creator->name}} · {{$invoice->created_at?->format('d/m/Y H:i')}}</div>
      @if($invoice->notes)<div>Catatan: {{$invoice->notes}}</div>@endif
      <div>Metode PPN: {{['manual'=>'Nominal sesuai dokumen PBF','none'=>'Tanpa PPN','standard'=>'DPP × tarif PPN','nilai_lain'=>'DPP nilai lain (11/12) × tarif'][$invoice->tax_mode]??'Pencatatan lama'}}</div>
      <div class="print-note">Salinan dibuat dari data internal yang diinput petugas. Gunakan lampiran faktur asli dari PBF sebagai bukti transaksi yang sah.</div>
    </div>
    <table class="money-table">
      <tr><td>Subtotal / bruto barang</td><td>Rp {{number_format($invoice->subtotal,2,',','.')}}</td></tr>
      <tr><td>Potongan per barang</td><td>− Rp {{number_format($invoice->line_discount_total,2,',','.')}}</td></tr>
      <tr><td>Diskon faktur</td><td>− Rp {{number_format($invoice->discount,2,',','.')}}</td></tr>
      <tr><td>DPP</td><td>Rp {{number_format((float)$invoice->dpp ?: max(0,$invoice->subtotal-$invoice->discount),2,',','.')}}</td></tr>
      @if($invoice->tax_mode==='nilai_lain')<tr><td>DPP Nilai Lain</td><td>Rp {{number_format($invoice->other_dpp,2,',','.')}}</td></tr>@endif
      <tr><td>PPN</td><td>Rp {{number_format($invoice->tax,2,',','.')}}</td></tr>
      <tr><td>Biaya kirim / lain</td><td>Rp {{number_format($invoice->shipping,2,',','.')}}</td></tr>
      <tr class="total"><td>TOTAL FAKTUR</td><td>Rp {{number_format($invoice->total,2,',','.')}}</td></tr>
      <tr><td>Sudah dibayar</td><td>Rp {{number_format($invoice->paid_amount,2,',','.')}}</td></tr>
      <tr><td>Sisa tagihan</td><td>Rp {{number_format($invoice->balance,2,',','.')}}</td></tr>
    </table>
  </div>
  <div class="footer"><span>Apotek Serenan · Salinan administrasi pembelian</span><span>Dicetak {{now()->format('d/m/Y H:i')}} · Nomor faktur: {{$invoice->invoice_no}}</span></div>
</main>
</body>
</html>
