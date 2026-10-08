@extends('layouts.app')
@section('title','Manajemen Pengguna')
@section('subtitle','Kelola akun petugas dan administrator yang dapat mengakses sistem.')
@section('actions')<a class="btn btn-primary" href="{{route('users.create')}}">＋ Tambah Pengguna</a>@endsection
@section('content')<div class="panel"><div class="table-scroll"><table><thead><tr><th>Nama</th><th>Email</th><th>Hak Akses</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($users as $u)<tr><td><strong>{{$u->name}}</strong></td><td>{{$u->email}}</td><td><span class="tag {{$u->isAdmin()?'tag-info':'tag-muted'}}">{{$u->isAdmin()?'Admin':'Petugas'}}</span></td><td><span class="tag {{$u->is_active?'tag-success':'tag-danger'}}">{{$u->is_active?'Aktif':'Nonaktif'}}</span></td><td><a class="text-link" href="{{route('users.edit',$u)}}">Edit</a></td></tr>@endforeach
</tbody></table></div>@include('partials.pagination',['paginator'=>$users])</div>@endsection
