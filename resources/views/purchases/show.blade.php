@extends('layouts.app')
@section('title','Detail Faktur '.$invoice->invoice_no)
@section('subtitle','Dokumen PBF, rincian diskon per barang, DPP/PPN, stok batch, dan pelunasan.')
@section('actions')
  <a href="{{route('purchases.print',$invoice)}}" class="btn btn-primary" target="_blank" rel="noopener">⎙ Cetak / Simpan PDF</a>
  <a href="{{route('purchases.index')}}" class="btn btn-light">← Kembali</a>
@endsection
@section('content')
<div class="detail-top">
  <section class="panel"><div class="panel-title"><div><h2>Informasi Faktur</h2><p>Pemasok dan tanggal penting</p></div><span class="tag {{$invoice->balance<=0?'tag-success':($invoice->due_date->isPast()?'tag-danger':'tag-warning')}}">{{$invoice->payment_status}}</span></div>
  <div class="detail-grid">
    <div><span>PBF</span><strong>{{$invoice->supplier->name}}</strong></div>
    <div><span>Nomor Faktur</span><strong>{{$invoice->invoice_no}}</strong></div>
    <div><span>Tanggal Faktur</span><strong>{{$invoice->invoice_date->format('d/m/Y')}}</strong></div>
    <div><span>Tanggal Barang Datang</span><strong>{{$invoice->received_date->format('d/m/Y')}}</strong></div>
    <div><span>Jatuh Tempo</span><strong>{{$invoice->due_date->format('d/m/Y')}}</strong></div>
    <div><span>Penerima</span><strong>{{$invoice->recipient_name ?: 'Apotek Serenan'}}</strong></div>
    <div><span>Petugas</span><strong>{{$invoice->creator->name}}</strong></div>
    <div><span>Jenis Diskon</span><strong>{{['none'=>'Tanpa Diskon','invoice'=>'Keseluruhan','per_item'=>'Per Item','combined'=>'Kombinasi / Faktur Lama'][$invoice->discount_mode] ?? '—'}}</strong></div>
    @if($invoice->settlement_date)<div><span>Tanggal Pelunasan</span><strong>{{$invoice->settlement_date->format('d/m/Y')}}</strong></div>@endif
    <div><span>Total Faktur Asli</span><strong>{{$invoice->document_total !== null ? 'Rp '.number_format($invoice->document_total,2,',','.') : 'Belum diinput'}}</strong></div>
    <div><span>Perhitungan PPN</span><strong>{{['manual'=>'Nominal sesuai faktur', 'none'=>'Tanpa PPN', 'standard'=>'DPP × tarif', 'nilai_lain'=>'DPP nilai lain 11/12 × tarif'][$invoice->tax_mode]??'Manual / data lama'}}</strong></div>
  </div>
  @if($invoice->notes)<p class="hint">Catatan: {{$invoice->notes}}</p>@endif
  </section>
  <section class="panel"><div class="panel-title"><h2>Rincian Nilai Faktur</h2></div>
  <div class="money-summary">
    <div><span>Subtotal bruto barang</span><strong>Rp {{number_format($invoice->subtotal,2,',','.')}}</strong></div>
    <div><span>Diskon per barang</span><strong>− Rp {{number_format($invoice->line_discount_total,2,',','.')}}</strong></div>
    <div><span>Diskon tambahan faktur</span><strong>− Rp {{number_format($invoice->discount,2,',','.')}}</strong></div>
    <div><span>DPP @if((float)$invoice->dpp === 0.0 && (float)$invoice->subtotal > 0)<small>(data faktur lama)</small>@endif</span><strong>Rp {{number_format((float)$invoice->dpp ?: max(0,$invoice->subtotal-$invoice->discount),2,',','.')}}</strong></div>
    @if($invoice->tax_mode==='nilai_lain')<div><span>DPP Nilai Lain (11/12)</span><strong>Rp {{number_format($invoice->other_dpp,2,',','.')}}</strong></div>@endif
    <div><span>Pajak / PPN</span><strong>Rp {{number_format($invoice->tax,2,',','.')}}</strong></div>
    <div><span>Pengiriman / biaya lain</span><strong>Rp {{number_format($invoice->shipping,2,',','.')}}</strong></div>
    <div class="emphasis"><span>Total Faktur</span><strong>Rp {{number_format($invoice->total,2,',','.')}}</strong></div>
    <div><span>Sudah Dibayar</span><strong>Rp {{number_format($invoice->paid_amount,2,',','.')}}</strong></div>
    <div class="emphasis outstanding"><span>Sisa Tagihan</span><strong>Rp {{number_format($invoice->balance,2,',','.')}}</strong></div>
  </div></section>
</div>
<section class="panel"><div class="panel-title"><div><h2>Daftar Barang Sesuai Faktur PBF</h2><p>Harga, diskon, batch/ED, konversi stok, dan jumlah netto</p></div></div>
  <div class="table-scroll"><table><thead><tr><th>Barang</th><th>ZB</th><th>Batch</th><th>ED</th><th>Qty</th><th>Masuk Stok</th><th>Harga/Kemasan</th><th>Bruto</th><th>Diskon %</th><th>Total Potongan</th><th>Netto</th><th>Harga Jual Arsip V3*</th></tr></thead><tbody>
    @foreach($invoice->items as $item)
      <tr><td><span class="code-tag">{{$item->product->sku}}</span><small class="block-muted">{{$item->product->name}}</small></td>
      <td>{{$item->zb_code ?? '—'}}</td><td>{{$item->batch->batch_number}}</td><td>{{$item->batch->expires_at?->format('d/m/Y') ?? '—'}}</td>
      <td>{{$item->purchase_quantity}} {{$item->purchase_unit}}</td><td>{{$item->quantity_base}} {{$item->product->unit}}</td>
      <td class="nowrap">Rp {{number_format($item->purchase_unit_cost,2,',','.')}}</td>
      <td class="nowrap">Rp {{number_format((float)$item->line_gross ?: ((float)$item->purchase_unit_cost*$item->purchase_quantity),2,',','.')}}</td>
      <td>{{number_format($item->line_discount_percent,2,',','.')}}%</td><td class="nowrap">Rp {{number_format($item->line_discount_total,2,',','.')}}</td><td class="nowrap"><strong>Rp {{number_format($item->line_total,2,',','.')}}</strong></td><td class="nowrap">{{$item->selling_unit_price === null ? '—' : 'Rp '.number_format($item->selling_unit_price,2,',','.')}}</td></tr>
    @endforeach
  </tbody></table></div>
  <p class="hint">*Harga jual arsip V3 hanya dokumentasi transaksi lama. Harga jual aktif sekarang diatur pada <a href="{{route('products.index')}}" class="text-link">Data Barang</a>.</p>
</section>
<div class="two-columns">
  <section class="panel"><div class="panel-title"><h2>Lampiran Faktur PBF Asli</h2></div>
    @if($invoice->attachment_path)
      <p>📎 <strong>{{$invoice->attachment_filename}}</strong></p>
      <a class="btn btn-light" href="{{route('purchases.attachment',$invoice)}}">↓ Unduh Lampiran Asli</a>
    @else
      <div class="empty-state">Belum ada lampiran faktur asli.</div>
    @endif
    @can('admin')
      <form class="form-stack attachment-form" method="post" enctype="multipart/form-data" action="{{route('purchases.upload-attachment',$invoice)}}">
        @csrf<label>{{$invoice->attachment_path?'Ganti':'Tambahkan'}} Lampiran<input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" required></label>
        <small class="muted">PDF / JPG / PNG / WEBP, maksimal 8 MB. Jika diganti, file sebelumnya akan dihapus.</small>
        <button class="btn btn-light" type="submit">Simpan Lampiran</button>
      </form>
    @endcan
  </section>
  <section class="panel"><div class="panel-title"><h2>Riwayat Pembayaran</h2></div>
    @forelse($invoice->payments->sortByDesc('paid_at') as $payment)
      <div class="alert-row"><span class="dot green-dot"></span><div class="alert-details"><strong>{{$payment->paid_at->format('d M Y')}} · {{ucfirst($payment->method)}}</strong><small>{{$payment->reference ?: ($payment->notes ?: 'Pembayaran tagihan')}}</small></div><strong>Rp {{number_format($payment->amount,0,',','.')}}</strong></div>
    @empty<div class="empty-state">Belum ada pembayaran.</div>@endforelse
  </section>
</div>
<section class="panel payment-panel"><div class="panel-title"><h2>Catat Pembayaran Faktur</h2></div>
@can('admin')
@if($invoice->balance > 0)
  <form method="post" action="{{route('payments.store',$invoice)}}" class="form-stack">@csrf
    <div class="form-grid">
      <label>Tanggal Bayar<input name="paid_at" type="date" value="{{old('paid_at',date('Y-m-d'))}}" required></label>
      <label>Nominal Pembayaran (Rp)<input name="amount" type="number" min="0.01" max="{{number_format($invoice->balance,2,'.','')}}" step="0.01" value="{{old('amount')}}" required placeholder="Maksimal {{number_format($invoice->balance,0,',','.')}}"></label>
      <label>Metode Bayar<select name="method" required><option value="transfer">Transfer</option><option value="tunai">Tunai</option><option value="lainnya">Lainnya</option></select></label>
      <label>Referensi / Bukti Transaksi<input name="reference" value="{{old('reference')}}" placeholder="Nomor transfer"></label>
    </div>
    <label>Catatan<textarea name="notes" rows="2">{{old('notes')}}</textarea></label>
    <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan Pembayaran</button></div>
  </form>
@else<div class="empty-state">✓ Tagihan telah lunas.</div>@endif
@else<div class="empty-state">Hanya admin yang dapat mencatat pelunasan PBF.</div>@endcan
</section>
@endsection
