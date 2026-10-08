@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle','Pantau aktivitas dan kesehatan stok Apotek Serenan hari ini.')
@section('actions')<a class="btn btn-primary" href="{{route('purchases.create')}}">＋ Barang Masuk</a><a class="btn btn-light" href="{{route('stock-outs.create')}}">↗ Barang Keluar</a>@endsection
@section('content')
<div class="welcome-banner"><div><span class="pill banner-pill">RINGKASAN OPERASIONAL</span><h2>Selamat datang, {{\Illuminate\Support\Str::words(auth()->user()->name,2,'')}}!</h2><p>Semua pergerakan barang dan pengingat tagihan tersaji dalam satu dashboard.</p></div><div class="banner-illustration" aria-hidden="true">✚</div></div>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-top"><span>Jenis Barang</span><span class="stat-icon mint">▤</span></div><div class="stat-value">{{$productCount}}</div><div class="stat-foot">Master produk terdaftar</div></div>
    <div class="stat-card"><div class="stat-top"><span>Stok Menipis / Habis</span><span class="stat-icon amber">!</span></div><div class="stat-value">{{$lowStockCount}}</div><div class="stat-foot">Stok layak jual ≤ batas minimum</div></div>
    <div class="stat-card"><div class="stat-top"><span>Mendekati Kedaluwarsa</span><span class="stat-icon pink">◷</span></div><div class="stat-value">{{$expiringCount}}</div><div class="stat-foot">Batch kedaluwarsa ≤ 90 hari</div></div>
    <div class="stat-card"><div class="stat-top"><span>Tagihan Terlambat</span><span class="stat-icon blue">Rp</span></div><div class="stat-value">{{$overdueCount}}</div><div class="stat-foot">Rp {{number_format($overdueBalance,0,',','.')}} belum dilunasi</div></div>
</div>
<div class="two-columns">
    <section class="panel"><div class="panel-title"><div><h2>⏰ Tagihan Mendekati Tempo</h2><p>Faktur yang perlu ditindaklanjuti dalam 7 hari</p></div><a class="text-link" href="{{route('payments.index')}}">Lihat semua →</a></div>
        @forelse($dueSoon as $invoice)
            <a class="alert-row" href="{{route('purchases.show',$invoice)}}"><span class="dot amber-dot"></span><div class="alert-details"><strong>{{$invoice->supplier->name}}</strong><small>{{$invoice->invoice_no}} · {{ $invoice->due_date->format('d M Y') }}</small></div><span class="alert-amount">Rp {{number_format($invoice->balance,0,',','.')}}</span></a>
        @empty <div class="empty-state">✓ Belum ada tagihan yang segera jatuh tempo.</div>@endforelse
    </section>
    <section class="panel"><div class="panel-title"><div><h2>⚠ Stok Perlu Perhatian</h2><p>Barang yang mencapai batas minimum</p></div><a class="text-link" href="{{route('stock.index')}}">Lihat stok →</a></div>
        @forelse($lowStock as $product)
            <a class="alert-row" href="{{route('stock.show',$product)}}"><span class="dot red-dot"></span><div class="alert-details"><strong>{{$product->name}}</strong><small>{{$product->sku}} · Minimum {{$product->minimum_stock}} {{$product->unit}}</small></div><span class="tag tag-danger">{{(int)$product->sellable_stock}} {{$product->unit}}</span></a>
        @empty <div class="empty-state">✓ Tidak ada barang di bawah batas minimum.</div>@endforelse
    </section>
</div>
<div class="two-columns">
    <section class="panel"><div class="panel-title"><div><h2>◷ Batch Akan Kedaluwarsa</h2><p>Perlu diperiksa dan diprioritaskan sesuai FEFO</p></div><a class="text-link" href="{{route('stock.index')}}">Seluruh stok →</a></div>
        @forelse($expiring as $batch)
            <a class="alert-row" href="{{route('stock.show',$batch->product)}}"><span class="dot amber-dot"></span><div class="alert-details"><strong>{{$batch->product->name}}</strong><small>Batch {{$batch->batch_number}} · {{$batch->quantity_available}} {{$batch->product->unit}}</small></div><span class="tag tag-warning">{{$batch->expires_at->format('d M Y')}}</span></a>
        @empty <div class="empty-state">Tidak ada batch yang akan kedaluwarsa dalam 90 hari.</div>@endforelse
    </section>
    <section class="panel"><div class="panel-title"><div><h2>↔ Aktivitas Terakhir</h2><p>Jejak pergerakan persediaan terbaru</p></div><a class="text-link" href="{{route('reports.index')}}">Lihat laporan →</a></div>
        @forelse($recentMovements as $movement)
            <div class="alert-row"><span class="dot {{$movement->quantity_change>=0?'green-dot':'red-dot'}}"></span><div class="alert-details"><strong>{{$movement->product->name}}</strong><small>{{$movement->created_at->format('d/m/Y H:i')}} · {{$movement->creator?->name}}</small></div><span class="movement {{$movement->quantity_change>=0?'positive':'negative'}}">{{$movement->quantity_change>0?'+':''}}{{$movement->quantity_change}}</span></div>
        @empty <div class="empty-state">Belum ada transaksi stok.</div>@endforelse
    </section>
</div>
@endsection
