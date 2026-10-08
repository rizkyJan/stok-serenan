@extends('layouts.app')
@section('title',$product->exists?'Edit Barang':'Tambah Barang')
@section('subtitle','Satuan dasar harus menjadi satuan terkecil yang digunakan untuk pengeluaran barang.')
@section('content')
<form method="post" action="{{$product->exists?route('products.update',$product):route('products.store')}}" class="panel form-card">@csrf @if($product->exists)@method('PUT')@endif
<div class="form-grid"><label>Kode Barang <span class="required">*</span><input name="sku" value="{{old('sku',$product->sku)}}" required maxlength="60" placeholder="Contoh: OBT0001"></label>
<label>Nama Barang <span class="required">*</span><input name="name" value="{{old('name',$product->name)}}" required placeholder="Contoh: Paracetamol 500 mg"></label>
<label>Kategori<input name="category" value="{{old('category',$product->category)}}" placeholder="Obat, vitamin, alat kesehatan..."></label>
<label>Satuan Dasar <span class="required">*</span><input name="unit" value="{{old('unit',$product->unit??'tablet')}}" required placeholder="tablet / strip / botol / pcs"></label>
<label>Batas Minimum Stok <span class="required">*</span><input name="minimum_stock" type="number" min="0" value="{{old('minimum_stock',$product->minimum_stock??10)}}" required></label>
<label>Status <select name="is_active"><option value="1" @selected(old('is_active',$product->exists?(int)$product->is_active:1)==1)>Aktif</option><option value="0" @selected(old('is_active',$product->exists?(int)$product->is_active:1)==0)>Nonaktif</option></select></label></div>
<label>Catatan <textarea name="notes" rows="3">{{old('notes',$product->notes)}}</textarea></label>
<div class="hint">Contoh: jika obat dijual per tablet, gunakan satuan dasar <strong>tablet</strong>. Saat pembelian 1 box berisi 100 tablet, isi pengali 100 pada form barang masuk.</div>
<div class="form-actions"><a class="btn btn-light" href="{{route('products.index')}}">Batal</a><button class="btn btn-primary" type="submit">Simpan Barang</button></div></form>
@endsection
