<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Masuk · Apotek Serenan</title><link rel="stylesheet" href="{{asset('css/apotek.css')}}"></head>
<body class="login-body"><div class="login-decoration"></div><main class="login-panel"><div class="login-symbol">✚</div><div class="eyebrow">SISTEM INVENTARIS</div><h1>Apotek Serenan</h1><p class="login-desc">Masuk untuk memantau barang masuk, pengeluaran, persediaan, dan tagihan PBF.</p>
    @if($errors->any())<div class="alert error">{{$errors->first()}}</div>@endif
    <form method="post" action="{{route('login.attempt')}}" class="form-stack">@csrf
        <label>Email pengguna <input type="email" name="email" value="{{old('email')}}" autocomplete="username" placeholder="nama@apotek.com" required autofocus></label>
        <label>Kata sandi <input type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required></label>
        <button class="btn btn-primary btn-block" type="submit">Masuk ke Dashboard <span>→</span></button>
    </form><div class="login-help">Belum memiliki akun? Hubungi administrator apotek.</div>
</main></body></html>
