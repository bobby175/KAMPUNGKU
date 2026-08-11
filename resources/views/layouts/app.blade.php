<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Ruang Warga Sa’ar Kleco')</title>
    <meta name="description" content="Portal informasi warga RT 002 / RW 003 Sa’ar Kleco: kas, agenda, pengumuman, dan dokumentasi kegiatan.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Ruang Warga Sa’ar Kleco">
    <meta property="og:title" content="@yield('title', 'Ruang Warga Sa’ar Kleco')">
    <meta property="og:description" content="Informasi warga yang tertata, transparan, dan mudah dijangkau dalam satu ruang bersama.">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    <meta property="og:image" content="{{ asset('images/social-share.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1728">
    <meta property="og:image:height" content="909">
    <meta property="og:image:alt" content="Ruang Warga Sa’ar Kleco, RT 002 RW 003">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Ruang Warga Sa’ar Kleco')">
    <meta name="twitter:description" content="Portal informasi dan kebersamaan warga Sa’ar Kleco.">
    <meta name="twitter:image" content="{{ asset('images/social-share.png') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ticker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
<header>
    <nav class="nav shell">
        <a class="brand" href="{{ route('home') }}"><b>RW</b><span>Ruang Warga Sa’ar Kleco<small>RT 002 · RW 003</small></span></a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-menu"><span></span><span></span><span></span><b>Menu</b></button>
        <button class="menu-backdrop" type="button" aria-label="Tutup menu"></button>
        <div class="links" id="site-menu">
            <div class="menu-head"><div><small>NAVIGASI WARGA</small><strong>RT 002 · RW 003</strong></div><button class="menu-close" type="button" aria-label="Tutup menu">×</button></div>
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><span>01</span><b>Beranda</b><small>Ringkasan portal warga</small></a>
            <a class="{{ request()->routeIs('cash') ? 'active' : '' }}" href="{{ route('cash') }}"><span>02</span><b>Kas RT</b><small>Saldo dan transaksi</small></a>
            <a class="{{ request()->routeIs('events') ? 'active' : '' }}" href="{{ route('events') }}"><span>03</span><b>Agenda</b><small>Jadwal kegiatan warga</small></a>
            <a class="{{ request()->routeIs('moments') ? 'active' : '' }}" href="{{ route('moments') }}"><span>04</span><b>Momen</b><small>Dokumentasi kebersamaan</small></a>
            @if(session('admin'))
                <form class="mobile-auth" method="post" action="{{ route('logout') }}">@csrf<button>Keluar admin</button></form>
            @else
                <a class="mobile-auth" href="{{ route('login') }}">Masuk admin</a>
            @endif
        </div>
        @if(session('admin'))
            <form class="desktop-auth" method="post" action="{{ route('logout') }}">@csrf<button class="btn outline">Keluar admin</button></form>
        @else
            <a class="btn outline desktop-auth" href="{{ route('login') }}">Masuk admin</a>
        @endif
    </nav>
</header>
@if(session('success'))<div class="flash">{{ session('success') }}</div>@endif
@isset($tickerAnnouncement)
<section class="announcement" aria-label="Pengumuman warga"><div class="shell"><b>PENGUMUMAN</b><div class="ticker"><div class="ticker-track"><span><strong class="ticker-manual">{{ $tickerAnnouncement }}</strong>@if($agendaTicker)<em class="ticker-agenda">◆ &nbsp;{{ $agendaTicker }}</em>@endif <i>◆</i></span><span aria-hidden="true"><strong class="ticker-manual">{{ $tickerAnnouncement }}</strong>@if($agendaTicker)<em class="ticker-agenda">◆ &nbsp;{{ $agendaTicker }}</em>@endif <i>◆</i></span></div></div></div></section>
@if(session('admin'))<section class="announcement-editor"><form class="shell" method="post" action="{{ route('admin.store', 'announcement') }}">@csrf<label for="announcement-text">Edit pengumuman</label><input id="announcement-text" name="announcement" value="{{ $data['announcement'] }}" maxlength="500" required><button class="btn copper" type="submit">Simpan teks</button><small>Agenda terdekat ditambahkan otomatis dari halaman Agenda.</small></form></section>@endif
@endisset
@yield('content')
<footer><div class="shell"><div class="brand inverse"><b>RW</b><span>Ruang Warga Sa’ar Kleco<small>RT 002 · RW 003</small></span></div><p>© {{ date('Y') }} RT 002 / RW 003 · Dibuat dengan semangat gotong royong.</p></div></footer>
<script>
(()=>{const toggle=document.querySelector('.nav-toggle');const menu=document.querySelector('#site-menu');const close=document.querySelector('.menu-close');const backdrop=document.querySelector('.menu-backdrop');if(!toggle||!menu)return;const setMenu=open=>{document.body.classList.toggle('menu-open',open);toggle.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-hidden',String(!open));if(open)setTimeout(()=>close?.focus(),280);else toggle.focus()};menu.setAttribute('aria-hidden','true');toggle.addEventListener('click',()=>setMenu(!document.body.classList.contains('menu-open')));close?.addEventListener('click',()=>setMenu(false));backdrop?.addEventListener('click',()=>setMenu(false));menu.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>setMenu(false)));document.addEventListener('keydown',event=>{if(event.key==='Escape'&&document.body.classList.contains('menu-open'))setMenu(false)})})();
</script>
</body>
</html>
