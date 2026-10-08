@extends('layouts.app')
@section('title',$user->exists?'Edit Pengguna':'Tambah Pengguna')
@section('subtitle','Kelola nama pengguna, kredensial masuk, serta izin akses.')
@section('content')<form method="post" action="{{$user->exists?route('users.update',$user):route('users.store')}}" class="panel form-card">@csrf @if($user->exists)@method('PUT')@endif
<div class="form-grid"><label>Nama Lengkap <span class="required">*</span><input name="name" value="{{old('name',$user->name)}}" required></label>
<label>Email <span class="required">*</span><input type="email" name="email" value="{{old('email',$user->email)}}" required></label>
<label>Kata Sandi {{$user->exists?'(biarkan kosong jika tidak diubah)':'*'}}<input type="password" name="password" autocomplete="new-password" minlength="10" {{$user->exists?'':'required'}} placeholder="Minimal 10 karakter"></label>
<label>Hak Akses<select name="role"><option value="petugas" @selected(old('role',$user->role??'petugas')==='petugas')>Petugas</option><option value="admin" @selected(old('role',$user->role)==='admin')>Administrator</option></select></label>
<label>Status Akun<select name="is_active"><option value="1" @selected(old('is_active',$user->exists?(int)$user->is_active:1)==1)>Aktif</option><option value="0" @selected(old('is_active',$user->exists?(int)$user->is_active:1)==0)>Nonaktif</option></select></label></div>
<div class="form-actions"><a class="btn btn-light" href="{{route('users.index')}}">Batal</a><button type="submit" class="btn btn-primary">Simpan Pengguna</button></div></form>@endsection
