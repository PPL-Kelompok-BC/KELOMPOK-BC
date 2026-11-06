@extends('layouts.auth')
@section('title', 'Masuk')
@section('nav')<a class="btn btn-primary" href="{{ route('register') }}">Daftar</a>@endsection
@section('content')
<section class="auth-card panel">
<div class="eyebrow">SELAMAT DATANG KEMBALI</div>
<h1>Masuk.</h1>
<p class="auth-lead">Masuk untuk melanjutkan challenge dan menjaga streak harianmu.</p>
<form method="POST" action="{{ route('login') }}" novalidate>
@csrf
<label class="field-label" for="email">Email</label>
<input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
@error('email')<p class="error" id="email-error" role="alert">{{ $message }}</p>@enderror

<label class="field-label" for="password">Kata sandi</label>
<input class="field" id="password" name="password" type="password" required autocomplete="current-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
@error('password')<p class="error" id="password-error" role="alert">{{ $message }}</p>@enderror

<label class="small field-hint" style="display:flex;gap:8px;align-items:center;margin-top:14px"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>

<button class="btn btn-primary full" style="margin-top:22px" type="submit">Masuk</button>
</form>
<p class="small auth-switch">Belum punya akun? <a href="{{ route('register') }}" class="text-btn">Daftar</a></p>
</section>
@endsection
