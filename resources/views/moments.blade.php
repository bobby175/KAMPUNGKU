@extends('layouts.app')
@section('title','Momen · Ruang Warga')
@section('content')
<main class="shell page">
    <div class="page-head"><p class="kicker">03 / DOKUMENTASI</p><h1>Momen warga.</h1><p>Foto, video, dan cerita yang membuat lingkungan terasa seperti rumah.</p></div>

    @if(!$driveConfigured && session('admin'))<div class="drive-notice"><b>Galeri Google Drive belum dihubungkan.</b><span>Isi Folder ID dan kredensial pada environment.</span></div>@endif
    @if($driveError && session('admin'))<div class="drive-notice error">{{ $driveError }} Periksa Folder ID, izin folder, dan kredensial.</div>@endif

    @if(count($driveAlbums))
    <section class="drive-section drive-albums">
        <div class="drive-heading"><div><p class="kicker">GALERI GOOGLE DRIVE</p><h2>Album kegiatan warga.</h2></div><span>{{ collect($driveAlbums)->sum(fn($album) => count($album['photos'])) }} foto</span></div>
        @foreach($driveAlbums as $album)
        <article class="drive-album">
            <div class="album-heading"><div><small>ALBUM {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</small><h3>{{ $album['title'] }}</h3></div><span>{{ count($album['photos']) }} foto</span></div>
            <div class="drive-grid">@foreach($album['photos'] as $photo)<button class="drive-photo" type="button" data-full="https://drive.google.com/thumbnail?id={{ $photo['id'] }}&sz=w2400" data-name="{{ $photo['name'] }}" aria-label="Lihat foto dari album {{ $album['title'] }}"><img src="https://drive.google.com/thumbnail?id={{ $photo['id'] }}&sz=w1200" alt="Foto {{ $album['title'] }}" loading="lazy" decoding="async"></button>@endforeach</div>
        </article>
        @endforeach
    </section>
    <dialog class="photo-lightbox" id="photo-lightbox"><button class="lightbox-close" type="button" aria-label="Tutup tampilan foto">×</button><figure><img src="" alt=""></figure></dialog>
    <script>(()=>{const modal=document.getElementById('photo-lightbox');if(!modal)return;const image=modal.querySelector('img');document.querySelectorAll('.drive-photo').forEach(button=>button.addEventListener('click',()=>{image.src=button.dataset.full;image.alt=button.dataset.name;modal.showModal()}));modal.querySelector('.lightbox-close').addEventListener('click',()=>modal.close());modal.addEventListener('click',event=>{if(event.target===modal)modal.close()});modal.addEventListener('close',()=>{image.src=''})})()</script>
    @endif

    @if(session('admin'))<details class="admin-box"><summary>+ Tambah momen</summary><form method="post" action="{{ route('admin.store','moments') }}">@csrf<div class="form-grid"><input name="date" placeholder="Tanggal" required><input name="title" placeholder="Judul" required><select name="media_type"><option value="photo">Foto</option><option value="video">Video</option></select><input type="url" name="media_url" placeholder="https://... (opsional)"></div><textarea name="description" placeholder="Cerita singkat" required></textarea><button class="btn copper">Simpan</button></form></details>@endif
    <div class="moment-grid">@foreach($data['moments'] as $item)<article><div class="media">@if(empty($item['media_url']))<span>{{ $item['icon'] ?? '✦' }}</span>@elseif(($item['media_type']??'photo')==='photo')<img src="{{ $item['media_url'] }}" alt="Foto {{ $item['title'] }}">@else @php(preg_match('/(?:youtu\.be\/|v=|shorts\/)([\w-]+)/',$item['media_url'],$yt)) @if(!empty($yt[1]))<iframe src="https://www.youtube-nocookie.com/embed/{{ $yt[1] }}" allowfullscreen></iframe>@else<video src="{{ $item['media_url'] }}" controls></video>@endif @endif</div><div class="copy"><small>{{ $item['date'] }}</small><h2>{{ $item['title'] }}</h2><p>{{ $item['description'] }}</p>@if(!empty($item['media_url']))<a href="{{ $item['media_url'] }}" target="_blank">BUKA MEDIA PENUH ↗</a>@endif</div>@if(session('admin'))<form method="post" action="{{ route('admin.destroy',['moments',$item['id']]) }}">@csrf @method('DELETE')<button class="danger">Hapus</button></form>@endif</article>@endforeach</div>
</main>
@endsection
