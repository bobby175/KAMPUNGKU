@extends('layouts.app')
@section('title', 'Login Admin · Ruang Warga')
@section('content')
<main class="login-page">
    <form class="login-card" method="post" action="{{ route('login.submit') }}">
        @csrf
        <p class="kicker">AREA PENGURUS</p>
        <h1>Masuk untuk mengelola.</h1>
        <label>Nama pengguna<input name="username" value="{{ old('username') }}" required autocomplete="username"></label>
        <label>Kata sandi<input name="password" type="password" required autocomplete="current-password"></label>
        @error('username')<p class="error">{{ $message }}</p>@enderror
        <button class="btn copper" type="submit">Masuk</button>
        <small>Gunakan akun pengurus yang telah dikonfigurasi.</small>
    </form>
</main>
@endsection
