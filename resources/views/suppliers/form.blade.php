@extends('layouts.app')
@section('title',$supplier->exists?'Edit PBF':'Tambah PBF')
@section('subtitle','PBF digunakan pada transaksi barang masuk dan pencatatan tagihan.')
@section('content')
<form method="post" action="{{$supplier->exists?route('suppliers.update',$supplier):route('suppliers.store')}}" class="panel form-card">@csrf @if($supplier->exists)@method('PUT')@endif
<div class="form-grid"><label>Nama PBF <span class="required">*</span><input name="name" value="{{old('name',$supplier->name)}}" required></label>
<label>Nama Kontak <input name="contact_name" value="{{old('contact_name',$supplier->contact_name)}}"></label>
<label>Nomor Telepon <input name="phone" value="{{old('phone',$supplier->phone)}}"></label>
<label>Status<select name="is_active"><option value="1" @selected(old('is_active',$supplier->exists?(int)$supplier->is_active:1)==1)>Aktif</option><option value="0" @selected(old('is_active',$supplier->exists?(int)$supplier->is_active:1)==0)>Nonaktif</option></select></label></div>
<label>NPWP PBF (opsional)<input name="npwp" value="{{old('npwp',$supplier->npwp)}}" maxlength="40" placeholder="Nomor NPWP sesuai dokumen PBF"></label><label>Alamat<textarea name="address" rows="3">{{old('address',$supplier->address)}}</textarea></label><label>Catatan<textarea name="notes" rows="3">{{old('notes',$supplier->notes)}}</textarea></label>
<div class="form-actions"><a class="btn btn-light" href="{{route('suppliers.index')}}">Batal</a><button class="btn btn-primary">Simpan PBF</button></div></form>
@endsection
