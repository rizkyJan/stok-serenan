@extends('layouts.app')
@section('title','Data PBF')
@section('subtitle','Kelola perusahaan besar farmasi / pemasok barang apotek.')
@section('actions')<a class="btn btn-primary" href="{{route('suppliers.create')}}">＋ Tambah PBF</a>@endsection
@section('content')
<div class="panel"><form class="filter-bar" method="get"><input type="search" name="q" value="{{request('q')}}" placeholder="Cari nama PBF..."><button class="btn btn-light">Cari</button></form>
<div class="table-scroll"><table><thead><tr><th>Nama PBF</th><th>Kontak</th><th>Telepon</th><th>Alamat</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($suppliers as $s)<tr><td><strong>{{$s->name}}</strong></td><td>{{$s->contact_name??'-'}}</td><td>{{$s->phone??'-'}}</td><td>{{$s->address??'-'}}</td><td><span class="tag {{$s->is_active?'tag-success':'tag-muted'}}">{{$s->is_active?'Aktif':'Nonaktif'}}</span></td><td><a class="text-link" href="{{route('suppliers.edit',$s)}}">Edit</a></td></tr>
@empty<tr><td colspan="6" class="empty-table">Belum ada data PBF.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$suppliers])</div>
@endsection
