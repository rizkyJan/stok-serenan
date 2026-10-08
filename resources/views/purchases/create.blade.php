@extends('layouts.app')
@section('title','Catat Faktur & Barang Masuk')
@section('subtitle','Salin detail faktur PBF: barang, batch/ED, diskon, DPP, PPN, jatuh tempo, dan lampiran asli.')
@section('content')
@if($suppliers->isEmpty() || $products->isEmpty())
  <div class="alert error">Buat minimal satu <a class="text-link" href="{{route('suppliers.create')}}">PBF</a> dan <a class="text-link" href="{{route('products.create')}}">barang</a> sebelum mencatat penerimaan.</div>
@endif
<form method="post" enctype="multipart/form-data" action="{{route('purchases.store')}}" id="purchaseForm" class="form-stack" data-purchase-form>
@csrf
<section class="panel form-card">
  <div class="section-heading"><span class="section-index">01</span><div><h2>Informasi Faktur PBF</h2><p>Nomor faktur, penerima, barang datang, dan jatuh tempo</p></div></div>
  <div class="form-grid">
    <label>Nama PBF <span class="required">*</span><select name="supplier_id" required><option value="">Pilih PBF</option>@foreach($suppliers as $s)<option value="{{$s->id}}" @selected(old('supplier_id')==$s->id)>{{$s->name}}</option>@endforeach</select></label>
    <label>Nomor Faktur <span class="required">*</span><input name="invoice_no" value="{{old('invoice_no')}}" maxlength="100" placeholder="Sesuai faktur PBF" required></label>
    <label>Tanggal Faktur <span class="required">*</span><input type="date" name="invoice_date" value="{{old('invoice_date',date('Y-m-d'))}}" required></label>
    <label>Tanggal Barang Datang <span class="required">*</span><input type="date" name="received_date" value="{{old('received_date',date('Y-m-d'))}}" required></label>
    <label>Jatuh Tempo Pembayaran <span class="required">*</span><input type="date" name="due_date" value="{{old('due_date',date('Y-m-d',strtotime('+30 days')))}}" required></label>
    <label>Apotek / Penerima <input name="recipient_name" value="{{old('recipient_name','Apotek Serenan')}}" maxlength="255" placeholder="Nama penerima di faktur"></label>
  </div>
  <label>Catatan Faktur<textarea name="notes" rows="2" placeholder="Nomor pesanan, keterangan penerimaan, dsb.">{{old('notes')}}</textarea></label>
</section>
<section class="panel form-card">
  <div class="section-heading"><span class="section-index">02</span><div><h2>Rincian Barang & Potongan</h2><p>Setiap barang bisa punya harga, diskon, batch dan tanggal ED sendiri</p></div></div>
  <div id="purchaseItems" class="item-container">
    @foreach(old('items', []) as $index => $item)
      @include('purchases._item', ['item'=>$item, 'index'=>$index, 'displayIndex'=>$loop->iteration])
    @endforeach
  </div>
  <div class="add-row"><button type="button" class="btn btn-light" id="addItem">＋ Tambah Barang ke Faktur</button><span>Maksimal 50 baris per faktur. Diskon (%) dan potongan tambahan (Rp) dihitung berurutan.</span></div>
</section>
<section class="panel form-card">
  <div class="section-heading"><span class="section-index">03</span><div><h2>Perhitungan Faktur & Pajak</h2><p>Nilai di preview dihitung ulang oleh server; cocokkan dengan nominal pada dokumen PBF</p></div></div>
  <div class="form-grid">
    <label>Diskon Tambahan Faktur (Rp)<input data-invoice-adjustment type="number" min="0" step="0.01" name="discount" value="{{old('discount',0)}}" required></label>
    <label>Biaya Kirim / Biaya Lain (Rp)<input data-invoice-adjustment type="number" min="0" step="0.01" name="shipping" value="{{old('shipping',0)}}" required></label>
    <label>Total pada Faktur Asli (Rp, opsional)<input type="number" name="document_total" min="0" max="999999999999" step="0.01" value="{{old('document_total')}}" data-invoice-adjustment placeholder="Gunakan untuk validasi nilai akhir"><small class="input-description">Jika diisi, sistem menolak transaksi bila total berbeda.</small></label>
    <label>Metode PPN<select name="tax_mode" id="taxMode">
      <option value="manual" @selected(old('tax_mode','manual')==='manual')>Manual (salin nominal PPN faktur)</option>
      <option value="none" @selected(old('tax_mode')==='none')>Tanpa PPN</option>
      <option value="standard" @selected(old('tax_mode')==='standard')>Otomatis: DPP × tarif PPN</option>
      <option value="nilai_lain" @selected(old('tax_mode')==='nilai_lain')>Otomatis: DPP Nilai Lain (11/12) × tarif</option>
    </select></label>
    <label>Tarif PPN (%)<input type="number" id="taxRate" min="0" max="100" step="0.001" name="tax_rate" value="{{old('tax_rate',12)}}" data-invoice-adjustment><small class="input-description">Untuk mode otomatis saja, cocokkan dengan dokumen PBF.</small></label>
    <label>Nominal PPN Manual (Rp)<input type="number" id="taxManual" min="0" step="0.01" name="tax" value="{{old('tax',0)}}" data-invoice-adjustment><small class="input-description">Dipakai hanya jika memilih mode manual.</small></label>
  </div>
  <div class="invoice-breakdown">
    <div><span>Subtotal bruto barang</span><strong id="calcGross">Rp 0</strong></div>
    <div><span>Total diskon per barang</span><strong id="calcLineDiscount">Rp 0</strong></div>
    <div><span>Diskon faktur</span><strong id="calcInvoiceDiscount">Rp 0</strong></div>
    <div><span>DPP (setelah seluruh diskon)</span><strong id="calcDpp">Rp 0</strong></div>
    <div><span>DPP Nilai Lain (jika dipilih)</span><strong id="calcOtherDpp">—</strong></div>
    <div><span>PPN</span><strong id="calcTax">Rp 0</strong></div>
    <div><span>Pengiriman / biaya lainnya</span><strong id="calcShipping">Rp 0</strong></div>
  </div>
  <div class="invoice-total"><span>Total Perkiraan Faktur</span><strong id="invoiceTotal">Rp 0</strong></div>
  <div class="hint" id="invoiceDocumentMatch" hidden></div>
  <div class="hint">Mode DPP Nilai Lain (11/12) adalah pilihan rumus pencatatan, bukan penetapan pajak otomatis. <strong>Nominal resmi tetap mengikuti faktur PBF.</strong> Gunakan PPN manual jika ada selisih pembulatan atau aturan khusus.</div>
</section>
<section class="panel form-card">
  <div class="section-heading"><span class="section-index">04</span><div><h2>Lampiran Faktur Asli</h2><p>Simpan scan/foto dari dokumen asli untuk pemeriksaan ulang</p></div></div>
  <label>Upload PDF / JPG / PNG / WEBP (opsional, maks. 8 MB)<input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"></label>
  <div class="hint">Berkas disimpan secara privat dan hanya bisa diunduh setelah login. Lampiran bisa ditambahkan belakangan dari halaman detail faktur.</div>
  <div class="form-actions"><a href="{{route('purchases.index')}}" class="btn btn-light">Batal</a><button type="submit" class="btn btn-primary" @disabled($products->isEmpty() || $suppliers->isEmpty())>Simpan Faktur & Tambah Stok</button></div>
</section>
</form>
<template id="purchaseItemTemplate">@include('purchases._item', ['item'=>[], 'index'=>'__INDEX__', 'displayIndex'=>''])</template>
@endsection
