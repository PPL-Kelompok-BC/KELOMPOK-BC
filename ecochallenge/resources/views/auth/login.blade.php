@extends('layouts.auth')
@section('title', 'Masuk')
@section('nav')<a class="btn btn-primary" href="{{ route('register') }}">Daftar</a>@endsection
@section('content')
<section class="auth-card panel">
<div class="eyebrow">SEGERA HADIR</div>
<h1>Masuk.</h1>
<p class="auth-lead">Halaman masuk dikerjakan pada PBI-04.</p>
<a class="btn btn-primary full" href="{{ route('register') }}">Buat akun baru</a>
</section>
@endsection
