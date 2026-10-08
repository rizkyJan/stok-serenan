@extends('layouts.app')
@section('title','Laporan Mutasi Stok')
@section('subtitle','Filter riwayat barang masuk, keluar, dan stok opname. Ekspor hasil ke CSV untuk Excel.')
@section('actions')<a class="btn btn-primary" href="{{route('reports.export',request()->only('from','to','product_id','type'))}}">↓ Ekspor CSV</a>@endsection
@section('content')
<div class="panel"><form method="get" class="report-filters"><label>Dari Tanggal<input type="date" name="from" value="{{request('from')}}"></label><label>Sampai Tanggal<input type="date" name="to" value="{{request('to')}}"></label>
<label>Barang<select name="product_id"><option value="">Semua barang</option>@foreach($products as $p)<option value="{{$p->id}}" @selected(request('product_id')==$p->id)>{{$p->sku}} — {{$p->name}}</option>@endforeach</select></label>
<label>Jenis Mutasi<select name="type"><option value="">Semua</option>@foreach(['in'=>'Barang Masuk','out'=>'Barang Keluar','adjustment_in'=>'Koreksi +','adjustment_out'=>'Koreksi -'] as $value=>$label)<option value="{{$value}}" @selected(request('type')===$value)>{{$label}}</option>@endforeach</select></label>
<button class="btn btn-light" type="submit">Terapkan Filter</button><a href="{{route('reports.index')}}" class="text-link">Reset</a></form>
<div class="table-scroll"><table><thead><tr><th>Waktu</th><th>Barang</th><th>Batch</th><th>Mutasi</th><th>Perubahan</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody>
@forelse($movements as $m)<tr><td class="nowrap">{{$m->created_at->format('d/m/Y H:i')}}</td><td><strong>{{$m->product->name}}</strong><small class="block-muted">{{$m->product->sku}}</small></td><td>{{$m->batch->batch_number}}</td><td>{{ucwords(str_replace('_',' ',$m->type))}}</td><td><span class="movement {{$m->quantity_change>=0?'positive':'negative'}}">{{$m->quantity_change>0?'+':''}}{{$m->quantity_change}} {{$m->product->unit}}</span></td><td>{{$m->creator?->name??'-'}}</td><td>{{$m->notes??'-'}}</td></tr>
@empty<tr><td colspan="7" class="empty-table">Belum ada transaksi pada periode ini.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$movements])</div>
@endsection
