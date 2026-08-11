@extends('layouts.app')
@section('title', 'Beranda · Ruang Warga Sa’ar Kleco')
@section('content')
<main>
    <section class="hero shell"><div><p class="kicker">PORTAL KOMUNITAS / SA’AR KLECO</p><h1>Tetangga dekat,<br><em>kebersamaan</em> erat.</h1><p class="lead">Informasi warga yang tertata, transparan, dan mudah dijangkau dalam satu ruang bersama.</p><div class="actions"><a class="btn copper" href="{{ route('events') }}">Lihat agenda</a><a class="text-link" href="{{ route('cash') }}">Buka laporan kas →</a></div></div><div class="hero-panel"><span>RT 002 / RW 003</span><strong>Ruang hidup<br>yang kita rawat<br>bersama.</strong><i>HARMONI · PEDULI · TRANSPARAN</i></div></section>

    @if(count($homePhotos))
    <section class="shell section home-moments">
        <div class="drive-heading"><div><p class="kicker">MOMEN TERBARU</p><h2>Kabar dalam gambar.</h2></div><a class="text-link" href="{{ route('moments') }}">LIHAT SEMUA →</a></div>
        <div class="drive-grid">@foreach($homePhotos as $photo)<button class="drive-photo home-drive-photo" type="button" data-full="https://drive.google.com/thumbnail?id={{ $photo['id'] }}&sz=w2400" data-name="{{ $photo['name'] }}" aria-label="Lihat foto {{ $photo['name'] }}"><img src="https://drive.google.com/thumbnail?id={{ $photo['id'] }}&sz=w1200" alt="{{ $photo['name'] }}" loading="lazy" decoding="async"></button>@endforeach</div>
    </section>
    <dialog class="photo-lightbox" id="home-photo-lightbox"><button class="lightbox-close" type="button" aria-label="Tutup tampilan foto">×</button><figure><img src="" alt=""></figure></dialog>
    <script>(()=>{const modal=document.getElementById('home-photo-lightbox');if(!modal)return;const image=modal.querySelector('img');document.querySelectorAll('.home-drive-photo').forEach(button=>button.addEventListener('click',()=>{image.src=button.dataset.full;image.alt=button.dataset.name;modal.showModal()}));modal.querySelector('.lightbox-close').addEventListener('click',()=>modal.close());modal.addEventListener('click',event=>{if(event.target===modal)modal.close()});modal.addEventListener('close',()=>{image.src=''})})()</script>
    @endif

    <section class="shell section"><div class="section-title"><p class="kicker">JELAJAHI PORTAL</p><h2>Informasi penting,<br>di halaman yang tepat.</h2></div><div class="menu-grid"><a href="{{ route('cash') }}"><small>01 / TRANSPARANSI</small><h3>Kas Warga</h3><p>Lihat saldo dan seluruh riwayat transaksi warga.</p><b>BUKA HALAMAN →</b></a><a href="{{ route('events') }}"><small>02 / KEBERSAMAAN</small><h3>Agenda</h3><p>Jadwal acara, kerja bakti, dan pelayanan warga.</p><b>BUKA HALAMAN →</b></a><a href="{{ route('moments') }}"><small>03 / DOKUMENTASI</small><h3>Momen</h3><p>Cerita, foto, dan video dari lingkungan kita.</p><b>BUKA HALAMAN →</b></a></div></section>
    @if(count($data['officials']) || session('admin'))
    <section class="shell section officials-section">
        <div class="section-title"><p class="kicker">PENGURUS LINGKUNGAN</p><h2>Nama pengurus<br>dan jabatan.</h2></div>
        @if(session('admin'))<details class="admin-box"><summary>+ Tambah pengurus</summary><form method="post" action="{{ route('admin.store', 'officials') }}">@csrf<div class="official-form"><input name="name" placeholder="Nama lengkap" maxlength="120" required><input name="position" placeholder="Jabatan, contoh: Ketua RT" maxlength="120" required><button class="btn copper" type="submit">Tambah</button></div></form></details>@endif
        @if(count($data['officials']))<div class="official-grid">@foreach($data['officials'] as $official)<article><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><small>{{ $official['position'] }}</small><h3>{{ $official['name'] }}</h3></div>@if(session('admin'))<form method="post" action="{{ route('admin.destroy', ['officials', $official['id']]) }}">@csrf @method('DELETE')<button class="danger" type="submit">Hapus</button></form>@endif</article>@endforeach</div>@else<p class="official-empty">Belum ada data pengurus. Tambahkan nama dan jabatan melalui mode admin.</p>@endif
    </section>
    @endif
</main>
@endsection
