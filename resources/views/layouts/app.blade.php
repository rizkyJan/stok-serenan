<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d544e">
    <title>@yield('title','Dashboard') · Apotek Serenan</title>
    <link rel="stylesheet" href="{{ asset('css/apotek.css') }}">
    <script src="{{ asset('js/apotek.js') }}" defer></script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-symbol">✚</span><span><strong>APOTEK<br>SERENAN</strong><small>Sistem Inventaris</small></span></a>
        <p class="nav-title">MENU UTAMA</p>
        <nav class="side-nav" aria-label="Menu utama">
            <a href="{{route('dashboard')}}" class="{{request()->routeIs('dashboard')?'active':''}}"><span>▦</span> Dashboard</a>
            <a href="{{route('products.index')}}" class="{{request()->routeIs('products.*')?'active':''}}"><span>▤</span> Data Barang</a>
            <a href="{{route('purchases.index')}}" class="{{request()->routeIs('purchases.*')?'active':''}}"><span>↘</span> Barang Masuk</a>
            <a href="{{route('stock-outs.index')}}" class="{{request()->routeIs('stock-outs.*')?'active':''}}"><span>↗</span> Barang Keluar</a>
            <a href="{{route('stock.index')}}" class="{{request()->routeIs('stock.*')?'active':''}}"><span>▥</span> Stok & Batch</a>
            <a href="{{route('suppliers.index')}}" class="{{request()->routeIs('suppliers.*')?'active':''}}"><span>⌂</span> Data PBF</a>
            <a href="{{route('payments.index')}}" class="{{request()->routeIs('payments.*')?'active':''}}"><span>◷</span> Tagihan PBF</a>
            <a href="{{route('reports.index')}}" class="{{request()->routeIs('reports.*')?'active':''}}"><span>▧</span> Laporan Stok</a>
        </nav>
        @can('admin')
            <p class="nav-title">ADMINISTRASI</p>
            <nav class="side-nav"><a href="{{route('users.index')}}" class="{{request()->routeIs('users.*')?'active':''}}"><span>♙</span> Pengguna</a></nav>
        @endcan
        <div class="sidebar-bottom"><span class="status-dot"></span> Aplikasi lokal · {{ now()->format('Y') }}<br><small>Data tersimpan di database apotek</small></div>
    </aside>
    <div class="mobile-overlay" id="mobileOverlay"></div>
    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left"><button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka menu">☰</button><span class="page-location">Operasional Apotek <span>/</span> @yield('title','Dashboard')</span></div>
            <div class="topbar-right"><span class="top-date">{{now()->translatedFormat('d F Y')}}</span><div class="avatar">{{strtoupper(\Illuminate\Support\Str::substr(auth()->user()->name,0,1))}}</div><span class="user-name"><strong>{{auth()->user()->name}}</strong><small>{{auth()->user()->isAdmin()?'Administrator':'Petugas'}}</small></span>
                <form method="post" action="{{route('logout')}}">@csrf<button class="logout-btn" type="submit" title="Keluar">Keluar</button></form>
            </div>
        </header>
        <main class="content">
            <div class="page-heading"><div><div class="eyebrow">APOTEK SERENAN / OPERASIONAL</div><h1>@yield('title','Dashboard')</h1><p>@yield('subtitle','Kelola persediaan barang dan operasional apotek dengan mudah.')</p></div><div class="heading-actions">@yield('actions')</div></div>
            @if(session('success'))<div class="alert success" role="status"><span>✓</span> {{session('success')}}</div>@endif
            @if($errors->any())<div class="alert error" role="alert"><strong>Periksa isian berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
            @yield('content')
            <footer class="footer">© {{date('Y')}} Apotek Serenan · Sistem Pencatatan Stok <span>Versi 1.2 · Penerimaan Barang</span></footer>
        </main>
    </div>
</div>
</body>
</html>
