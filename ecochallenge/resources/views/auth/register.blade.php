@extends('layouts.auth')
@section('title', 'Daftar')
@section('nav')<a class="btn" href="{{ route('login') }}">Masuk</a>@endsection
@section('content')
<section class="auth-card panel">
<div class="eyebrow">MULAI DARI SINI</div>
<h1>Buat akunmu.</h1>
<p class="auth-lead">Pilih username untuk nama tampilanmu di leaderboard. Nama aslimu tidak ditampilkan di sana.</p>
<form method="POST" action="{{ route('register') }}" novalidate>
@csrf
<label class="field-label" for="name">Nama lengkap</label>
<input class="field" id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required autofocus autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
@error('name')<p class="error" id="name-error" role="alert">{{ $message }}</p>@enderror

<label class="field-label" for="username">Username</label>
<input class="field" id="username" name="username" type="text" value="{{ old('username') }}" minlength="3" maxlength="30" required autocomplete="username" autocapitalize="none" @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
@error('username')<p class="error" id="username-error" role="alert">{{ $message }}</p>@else<p class="small field-hint">Huruf kecil, angka, titik, dan garis bawah. 3–30 karakter.</p>@enderror

<label class="field-label" for="email">Email</label>
<input class="field" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
@error('email')<p class="error" id="email-error" role="alert">{{ $message }}</p>@enderror

<label class="field-label" for="password">Kata sandi</label>
<input class="field" id="password" name="password" type="password" minlength="8" required autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
@error('password')<p class="error" id="password-error" role="alert">{{ $message }}</p>@else<p class="small field-hint">Minimal 8 karakter.</p>@enderror

<label class="field-label" for="password_confirmation">Ulangi kata sandi</label>
<input class="field" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">

<button class="btn btn-primary full" style="margin-top:24px" type="submit">Daftar</button>
</form>
<p class="small auth-switch">Sudah punya akun? <a href="{{ route('login') }}" class="text-btn">Masuk</a></p>
</section>
@endsection
