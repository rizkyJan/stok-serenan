@extends('layouts.app')
@section('title','Data Barang')
@section('subtitle','Daftarkan obat dan barang lainnya beserta satuan stok dasar.')
@section('actions')<a class="btn btn-primary" href="{{route('products.create')}}">＋ Tambah Barang</a>@endsection
@section('content')
<div class="panel"><form class="filter-bar" method="get"><input type="search" name="q" value="{{request('q')}}" placeholder="Cari kode atau nama barang..."><button class="btn btn-light">Cari</button></form>
<div class="table-scroll"><table><thead><tr><th>Kode Barang</th><th>Nama Barang</th><th>Kategori</th><th>Harga Jual / Satuan</th><th>Stok Layak Keluar</th><th>Min. Stok</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($products as $p)<tr><td><span class="code-tag">{{$p->sku}}</span></td><td><strong>{{$p->name}}</strong><small class="block-muted">Satuan: {{$p->unit}}</small></td><td>{{$p->category??'-'}}</td><td class="nowrap">{{$p->selling_price === null ? 'Belum diatur' : 'Rp '.number_format($p->selling_price,2,',','.')}} <small class="block-muted">/ {{$p->unit}}</small></td><td><strong>{{(int)$p->sellable_stock}}</strong> {{$p->unit}}</td><td>{{$p->minimum_stock}}</td><td><span class="tag {{$p->is_active?'tag-success':'tag-muted'}}">{{$p->is_active?'Aktif':'Nonaktif'}}</span></td><td class="nowrap"><a class="text-link" href="{{route('products.edit',$p)}}">Edit</a> · <a class="text-link" href="{{route('stock.show',$p)}}">Batch</a></td></tr>
@empty<tr><td class="empty-table" colspan="8">Belum ada barang. Gunakan tombol Tambah Barang untuk memulai.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$products])</div>
@endsection
