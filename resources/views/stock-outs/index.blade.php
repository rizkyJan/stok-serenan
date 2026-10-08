@extends('layouts.app')
@section('title','Barang Keluar')
@section('subtitle','Pengeluaran otomatis memotong stok batch dengan FEFO (masa kedaluwarsa terdekat dahulu).')
@section('actions')<a class="btn btn-primary" href="{{route('stock-outs.create')}}">＋ Catat Barang Keluar</a>@endsection
@section('content')
<div class="panel"><form method="get" class="filter-bar"><input type="search" name="q" value="{{request('q')}}" placeholder="Cari kode / nama barang..."><button class="btn btn-light">Cari</button></form>
<div class="table-scroll"><table><thead><tr><th>Waktu</th><th>Barang</th><th>Jumlah</th><th>Alasan</th><th>Penerima</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody>
@forelse($outs as $o)<tr><td class="nowrap">{{$o->created_at->format('d/m/Y H:i')}}</td><td><strong>{{$o->product->name}}</strong><small class="block-muted">{{$o->product->sku}}</small></td><td><strong>{{$o->quantity}}</strong> {{$o->product->unit}}</td><td><span class="tag tag-muted">{{ucwords(str_replace('_',' ',$o->reason))}}</span></td><td>{{$o->recipient??'-'}}</td><td>{{$o->creator->name}}</td><td>{{$o->notes??'-'}}</td></tr>
@empty<tr><td colspan="7" class="empty-table">Belum ada barang keluar.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$outs])</div>
@endsection
