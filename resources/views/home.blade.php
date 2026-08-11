@extends('layouts.app')
@section('title', 'Beranda · Ruang Warga Sa’ar Kleco')
@section('content')
@php($home = $data['home_content'])
<main>
    @if(session('admin'))
    <section class="shell home-editor-wrap">
        <details class="admin-box home-editor">
            <summary>✎ Edit seluruh teks Beranda</summary>
            <form method="post" action="{{ route('admin.store', 'home-content') }}">
                @csrf
                @php($homeFields = [
                    'hero_kicker'=>'Label pembuka',
                    'hero_title_line_1'=>'Judul baris 1',
                    'hero_title_emphasis'=>'Judul berwarna',
                    'hero_title_line_3'=>'Judul baris 3',
                    'hero_lead'=>'Deskripsi utama',
                    'agenda_button'=>'Tombol agenda',
                    'cash_button'=>'Tautan kas',
                    'panel_label'=>'Label panel',
                    'panel_title'=>'Judul panel',
                    'panel_tagline'=>'Slogan panel',
                    'moments_kicker'=>'Label momen terbaru',
                    'moments_title'=>'Judul momen terbaru',
                    'moments_link'=>'Tautan semua momen',
                    'portal_kicker'=>'Label jelajahi portal',
                    'portal_title'=>'Judul jelajahi portal',
                    'cash_label'=>'Label kartu kas',
                    'cash_title'=>'Judul kartu kas',
                    'cash_description'=>'Deskripsi kartu kas',
                    'agenda_label'=>'Label kartu agenda',
                    'agenda_title'=>'Judul kartu agenda',
                    'agenda_description'=>'Deskripsi kartu agenda',
                    'moments_label'=>'Label kartu momen',
                    'moments_card_title'=>'Judul kartu momen',
                    'moments_description'=>'Deskripsi kartu momen',
                    'card_action'=>'Teks buka halaman',
                    'officials_kicker'=>'Label pengurus',
                    'officials_title'=>'Judul pengurus',
                ])
                <div class="home-editor-grid">
                    @foreach($homeFields as $key => $label)
                    <label><span>{{ $label }}</span>
                        @if(str_contains($key, 'description') || in_array($key, ['hero_lead','panel_title','portal_title','officials_title']))
                        <textarea name="home[{{ $key }}]" required maxlength="500">{{ $home[$key] }}</textarea>
                        @else
                        <input name="home[{{ $key }}]" value="{{ $home[$key] }}" required maxlength="500">
                        @endif
                    </label>
                    @endforeach
                </div>
                <button class="btn copper" type="submit">Simpan seluruh teks</button>
            </form>
        </details>
    </section>
    @endif

    <section class="hero shell">
        <div>
            <p class="kicker">{{ $home['hero_kicker'] }}</p>
            <h1>{{ $home['hero_title_line_1'] }}<br><em>{{ $home['hero_title_emphasis'] }}</em><br>{{ $home['hero_title_line_3'] }}</h1>
            <p class="lead">{{ $home['hero_lead'] }}</p>
            <div class="actions"><a class="btn copper" href="{{ route('events') }}">{{ $home['agenda_button'] }}</a><a class="text-link" href="{{ route('cash') }}">{{ $home['cash_button'] }} →</a></div>
        </div>
        <div class="hero-panel"><span>{{ $home['panel_label'] }}</span><strong>{!! nl2br(e($home['panel_title'])) !!}</strong><i>{{ $home['panel_tagline'] }}</i></div>
    </section>

    @if(count($homePhotos))
    <section class="shell section home-moments">
        <div class="drive-heading"><div><p class="kicker">{{ $home['moments_kicker'] }}</p><h2>{{ $home['moments_title'] }}</h2></div><a class="text-link" href="{{ route('moments') }}">{{ $home['moments_link'] }} →</a></div>
        <div class="drive-grid">@foreach($homePhotos as $photo)<button class="drive-photo home-drive-photo" type="button" data-full="https://drive.google.com/thumbnail?id={{ $photo['id'] }}&sz=w2400" data-name="{{ $photo['name'] }}" aria-label="Lihat foto {{ $photo['name'] }}"><img src="https://drive.google.com/thumbnail?id={{ $photo['id'] }}&sz=w1200" alt="{{ $photo['name'] }}" loading="lazy" decoding="async"></button>@endforeach</div>
    </section>
    <dialog class="photo-lightbox" id="home-photo-lightbox"><button class="lightbox-close" type="button" aria-label="Tutup tampilan foto">×</button><figure><img src="" alt=""></figure></dialog>
    <script>(()=>{const modal=document.getElementById('home-photo-lightbox');if(!modal)return;const image=modal.querySelector('img');document.querySelectorAll('.home-drive-photo').forEach(button=>button.addEventListener('click',()=>{image.src=button.dataset.full;image.alt=button.dataset.name;modal.showModal()}));modal.querySelector('.lightbox-close').addEventListener('click',()=>modal.close());modal.addEventListener('click',event=>{if(event.target===modal)modal.close()});modal.addEventListener('close',()=>{image.src=''})})()</script>
    @endif

    <section class="shell section">
        <div class="section-title"><p class="kicker">{{ $home['portal_kicker'] }}</p><h2>{!! nl2br(e($home['portal_title'])) !!}</h2></div>
        <div class="menu-grid">
            <a href="{{ route('cash') }}"><small>{{ $home['cash_label'] }}</small><h3>{{ $home['cash_title'] }}</h3><p>{{ $home['cash_description'] }}</p><b>{{ $home['card_action'] }} →</b></a>
            <a href="{{ route('events') }}"><small>{{ $home['agenda_label'] }}</small><h3>{{ $home['agenda_title'] }}</h3><p>{{ $home['agenda_description'] }}</p><b>{{ $home['card_action'] }} →</b></a>
            <a href="{{ route('moments') }}"><small>{{ $home['moments_label'] }}</small><h3>{{ $home['moments_card_title'] }}</h3><p>{{ $home['moments_description'] }}</p><b>{{ $home['card_action'] }} →</b></a>
        </div>
    </section>

    @if(count($data['officials']) || session('admin'))
    <section class="shell section officials-section">
        <div class="section-title"><p class="kicker">{{ $home['officials_kicker'] }}</p><h2>{!! nl2br(e($home['officials_title'])) !!}</h2></div>
        @if(session('admin'))<details class="admin-box"><summary>+ Tambah pengurus</summary><form method="post" action="{{ route('admin.store', 'officials') }}">@csrf<div class="official-form"><input name="name" placeholder="Nama lengkap" maxlength="120" required><input name="position" placeholder="Jabatan, contoh: Ketua RT" maxlength="120" required><button class="btn copper" type="submit">Tambah</button></div></form></details>@endif
        @if(count($data['officials']))<div class="official-grid">@foreach($data['officials'] as $official)<article><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><small>{{ $official['position'] }}</small><h3>{{ $official['name'] }}</h3></div>@if(session('admin'))<form method="post" action="{{ route('admin.destroy', ['officials', $official['id']]) }}">@csrf @method('DELETE')<button class="danger" type="submit">Hapus</button></form>@endif</article>@endforeach</div>@else<p class="official-empty">Belum ada data pengurus. Tambahkan nama dan jabatan melalui mode admin.</p>@endif
    </section>
    @endif
</main>
@endsection
