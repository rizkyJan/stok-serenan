@extends('layouts.app')
@section('title','Tagihan PBF')
@section('subtitle','Pantau pelunasan faktur, tanggal tempo, dan sisa hutang supplier.')
@section('content')
<div class="panel"><div class="chip-filters"><a class="chip {{!request('status')?'selected':''}}" href="{{route('payments.index')}}">Semua</a><a class="chip {{request('status')==='unpaid'?'selected':''}}" href="{{route('payments.index',['status'=>'unpaid'])}}">Belum Lunas</a><a class="chip {{request('status')==='overdue'?'selected':''}}" href="{{route('payments.index',['status'=>'overdue'])}}">Terlambat</a><a class="chip {{request('status')==='paid'?'selected':''}}" href="{{route('payments.index',['status'=>'paid'])}}">Lunas</a></div>
<div class="table-scroll"><table><thead><tr><th>No. Faktur</th><th>Nama PBF</th><th>Jatuh Tempo</th><th>Total</th><th>Dibayar</th><th>Sisa Tagihan</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($invoices as $i)<tr><td><strong>{{$i->invoice_no}}</strong></td><td>{{$i->supplier->name}}</td><td>{{$i->due_date->format('d/m/Y')}}</td><td>Rp {{number_format($i->total,0,',','.')}}</td><td>Rp {{number_format($i->paid_amount,0,',','.')}}</td><td><strong>Rp {{number_format($i->balance,0,',','.')}}</strong></td><td><span class="tag {{$i->balance<=0?'tag-success':($i->due_date->lt(today())?'tag-danger':'tag-warning')}}">{{$i->balance>0 && $i->due_date->lt(today())?'Terlambat':$i->payment_status}}</span></td><td><a class="text-link" href="{{route('purchases.show',$i)}}">{{$i->balance>0?'Bayar / Detail':'Detail'}} →</a></td></tr>
@empty<tr><td colspan="8" class="empty-table">Tidak ada tagihan pada filter ini.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$invoices])</div>
@endsection
