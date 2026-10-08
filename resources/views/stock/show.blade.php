@extends('layouts.app')
@section('title','Detail Stok '.$product->name)
@section('subtitle','Stok per batch dan histori perubahan; hanya admin dapat mencatat koreksi stok opname.')
@section('actions')<a href="{{route('stock.index')}}" class="btn btn-light">← Kembali</a>@endsection
@section('content')
<div class="panel"><div class="panel-title"><h2>{{$product->sku}} · {{$product->name}}</h2><span class="tag tag-muted">Satuan dasar: {{$product->unit}}</span></div>
<div class="mini-stats"><div><span>Stok Fisik</span><strong>{{$product->batches->sum('quantity_available')}} {{$product->unit}}</strong></div><div><span>Layak Keluar</span><strong>{{$product->batches->filter(fn($b)=>!$b->expires_at||$b->expires_at->isToday()||$b->expires_at->isFuture())->sum('quantity_available')}} {{$product->unit}}</strong></div><div><span>Batas Minimum</span><strong>{{$product->minimum_stock}} {{$product->unit}}</strong></div></div>
<div class="table-scroll"><table><thead><tr><th>Nomor Batch</th><th>Kedaluwarsa</th><th>Jumlah Masuk Awal</th><th>Sisa Stok</th><th>Harga Beli / {{$product->unit}}</th><th>Status</th><th>Stok Opname</th></tr></thead><tbody>
@forelse($product->batches as $batch)<tr><td><strong>{{$batch->batch_number}}</strong></td><td>{{$batch->expires_at?->format('d/m/Y')??'Tidak dicatat'}}</td><td>{{$batch->quantity_received}}</td><td><strong>{{$batch->quantity_available}}</strong></td><td>Rp {{number_format($batch->unit_cost,2,',','.')}}</td><td><span class="tag {{$batch->expires_at && $batch->expires_at->isPast() && !$batch->expires_at->isToday()?'tag-danger':'tag-success'}}">{{$batch->expires_at && $batch->expires_at->isPast() && !$batch->expires_at->isToday()?'Kedaluwarsa':'Tersedia'}}</span></td><td>
@can('admin')<details class="correction"><summary>Koreksi</summary><form method="post" action="{{route('stock.adjust',$batch)}}" class="small-form" onsubmit="return confirm('Simpan perubahan stok batch ini?')">@csrf<label>Stok fisik hasil hitung<input type="number" min="0" name="new_quantity" value="{{$batch->quantity_available}}" required></label><label>Alasan<input name="reason" minlength="8" maxlength="2000" required placeholder="Hasil stok opname tanggal..."></label><button class="btn btn-primary btn-small">Simpan Koreksi</button></form></details>
@else <span class="muted">Admin saja</span> @endcan</td></tr>
@empty<tr><td colspan="7" class="empty-table">Belum ada batch. Masukkan barang terlebih dahulu.</td></tr>@endforelse
</tbody></table></div></div>
<section class="panel"><div class="panel-title"><h2>Riwayat Mutasi Stok</h2></div><div class="table-scroll"><table><thead><tr><th>Waktu</th><th>Batch</th><th>Jenis</th><th>Perubahan</th><th>Petugas</th><th>Keterangan</th></tr></thead><tbody>
@forelse($movements as $m)<tr><td class="nowrap">{{$m->created_at->format('d/m/Y H:i')}}</td><td>{{$m->batch->batch_number}}</td><td>{{ucwords(str_replace('_',' ',$m->type))}}</td><td><span class="movement {{$m->quantity_change>=0?'positive':'negative'}}">{{$m->quantity_change>0?'+':''}}{{$m->quantity_change}}</span></td><td>{{$m->creator?->name??'-'}}</td><td>{{$m->notes??'-'}}</td></tr>
@empty<tr><td colspan="6" class="empty-table">Belum ada histori perubahan stok.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination',['paginator'=>$movements])</section>
@endsection
