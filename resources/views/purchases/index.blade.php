@extends('layouts.app')
@section('title','Barang Masuk')
@section('subtitle','Daftar faktur penerimaan barang. Setiap faktur dapat memuat banyak barang.')
@section('actions')<a class="btn btn-primary" href="{{route('purchases.create')}}">＋ Catat Barang Masuk</a>@endsection
@section('content')
@if($drafts->isNotEmpty())
<section class="panel" style="margin-bottom:18px"><div class="panel-title"><h2>Draft Penerimaan Saya</h2><p>Belum menambah stok maupun tagihan</p></div>
<div class="table-scroll"><table><thead><tr><th>Nomor Faktur</th><th>Terakhir Disimpan</th><th>Aksi</th></tr></thead><tbody>
@foreach($drafts as $draft)<tr><td>{{$draft->invoice_no ?: 'Belum diberi nomor'}}</td><td>{{$draft->updated_at->format('d/m/Y H:i')}}</td><td style="display:flex;align-items:center;gap:8px"><a class="text-link" href="{{route('purchases.draft.edit',$draft)}}">Lanjutkan</a><form method="post" action="{{route('purchases.draft.delete',$draft)}}" onsubmit="return confirm('Hapus draft ini?')">@csrf @method('DELETE')<button type="submit" class="btn btn-light">Hapus</button></form></td></tr>@endforeach
</tbody></table></div></section>
@endif
<div class="panel"><form method="get" class="filter-bar"><input name="q" type="search" value="{{request('q')}}" placeholder="Cari nomor faktur atau PBF..."><button class="btn btn-light">Cari</button></form>
<div class="table-scroll"><table><thead><tr><th>Faktur</th><th>PBF</th><th>Tanggal Datang</th><th>Jatuh Tempo</th><th>Total Faktur</th><th>Status Bayar</th><th></th></tr></thead><tbody>
@forelse($invoices as $i)<tr><td><strong>{{$i->invoice_no}}</strong></td><td>{{$i->supplier->name}}</td><td>{{$i->received_date->format('d/m/Y')}}</td><td>{{$i->due_date->format('d/m/Y')}}</td><td class="nowrap">Rp {{number_format($i->total,0,',','.')}}</td><td><span class="tag {{$i->balance<=0?'tag-success':($i->due_date->lt(today())?'tag-danger':'tag-warning')}}">{{$i->payment_status}}</span></td><td><a class="text-link" href="{{route('purchases.show',$i)}}">Detail →</a></td></tr>
@empty<tr><td colspan="7" class="empty-table">Belum ada faktur barang masuk.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$invoices])</div>
@endsection
