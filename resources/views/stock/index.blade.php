@extends('layouts.app')
@section('title','Stok Barang')
@section('subtitle','Stok fisik, stok layak keluar, dan detail batch obat terpantau secara real time.')
@section('content')
<div class="panel"><form method="get" class="filter-bar"><input type="search" name="q" value="{{request('q')}}" placeholder="Cari nama / kode barang..."><button class="btn btn-light">Cari</button></form>
<div class="table-scroll"><table><thead><tr><th>Kode Barang</th><th>Nama Barang</th><th>Stok Fisik</th><th>Layak Keluar</th><th>Tidak Layak Keluar*</th><th>Batas Min.</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($products as $p)@php($sellable=(int)$p->sellable_stock)@php($physical=(int)$p->total_stock)<tr><td><span class="code-tag">{{$p->sku}}</span></td><td><strong>{{$p->name}}</strong><small class="block-muted">{{$p->category??'-'}}</small></td><td>{{$physical}} {{$p->unit}}</td><td><strong>{{$sellable}}</strong> {{$p->unit}}</td><td>{{$physical-$sellable}}</td><td>{{$p->minimum_stock}}</td><td><span class="tag {{$sellable<=$p->minimum_stock?'tag-danger':'tag-success'}}">{{$sellable<=$p->minimum_stock?'Perlu Restok':'Aman'}}</span></td><td><a class="text-link" href="{{route('stock.show',$p)}}">Lihat Batch →</a></td></tr>
@empty<tr><td colspan="8" class="empty-table">Data stok masih kosong.</td></tr>@endforelse
</tbody></table></div><p class="table-note">* Barang dengan tanggal kedaluwarsa sudah lewat; tetap dihitung secara fisik sampai disesuaikan/dikeluarkan.</p>@include('partials.pagination',['paginator'=>$products])</div>
@endsection
