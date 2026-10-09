@extends('layouts.app')
@section('title','Catat Barang Keluar')
@section('subtitle','Cari barang, isi jumlah satuan dasar, dan sistem otomatis memilih batch FEFO.')
@section('content')
<form method="post" action="{{route('stock-outs.store')}}" class="panel form-card" id="outForm">@csrf
<div class="form-grid"><label>Pilih Kode / Nama Barang <span class="required">*</span><select name="product_id" id="outProduct" required><option value="">Pilih barang...</option>@foreach($products as $p)<option value="{{$p->id}}" data-stock="{{(int)$p->sellable_stock}}" data-unit="{{$p->unit}}" data-price="{{$p->selling_price}}" @selected(old('product_id')==$p->id)>{{$p->sku}} — {{$p->name}}</option>@endforeach</select></label>
<label>Alasan Pengeluaran <span class="required">*</span><select name="reason" id="outReason" required><option value="penjualan" @selected(old('reason')==='penjualan')>Penjualan</option><option value="pemakaian" @selected(old('reason')==='pemakaian')>Pemakaian internal</option><option value="retur_pbf" @selected(old('reason')==='retur_pbf')>Retur ke PBF (stok saja)</option><option value="rusak" @selected(old('reason')==='rusak')>Barang rusak</option><option value="lainnya" @selected(old('reason')==='lainnya')>Lainnya</option></select></label>
<label>Jumlah Keluar (satuan dasar) <span class="required">*</span><input type="number" id="outQuantity" min="1" name="quantity" value="{{old('quantity',1)}}" required></label>
<label>Harga Jual / Satuan (dari Data Barang)
  <output id="outUnitPrice" class="readout">—</output>
  <small class="input-description">Harga aktif dikelola di menu Data Barang, bukan pada faktur pembelian.</small>
</label>
<label>Penerima / Tujuan <input name="recipient" value="{{old('recipient')}}" placeholder="Opsional"></label></div>
<label>Catatan <textarea name="notes" rows="3" placeholder="Keterangan tambahan (opsional)">{{old('notes')}}</textarea></label>
<div class="stock-preview"><div><span>Stok Layak Keluar</span><strong id="currentStock">—</strong></div><div><span>Jumlah Akan Dikeluarkan</span><strong id="outPreview">—</strong></div><div><span>Sisa Perkiraan</span><strong id="stockAfter">—</strong></div></div>
<div class="stock-preview"><div><span>Estimasi Nilai Penjualan</span><strong id="outSaleTotal">—</strong></div><div><span>Satuan harga</span><strong id="outPriceUnit">—</strong></div></div>
<div class="hint">Nilai penjualan hanya ditampilkan untuk alasan <strong>Penjualan</strong>. Sistem stok ini bukan kasir/POS dan belum mencatat penerimaan uang.</div>
<div class="hint">Barang kedaluwarsa tidak digunakan dalam pengeluaran normal. Transaksi barang keluar dan pembagian batch tersimpan permanen di riwayat.</div>
<div class="form-actions"><a href="{{route('stock-outs.index')}}" class="btn btn-light">Batal</a><button type="submit" class="btn btn-primary">Simpan & Kurangi Stok</button></div>
</form>
@endsection
