@extends('layouts.auth')
@section('title', 'Dashboard Admin')
@section('nav')
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">Keluar</button></form>
@endsection
@section('content')
<section class="auth-card panel">
<div class="eyebrow">DASHBOARD ADMIN</div>
<h1>Halo, {{ auth()->user()->name }}.</h1>
<p class="auth-lead">Kamu masuk sebagai <strong>admin</strong>. Fitur tinjau klaim, kelola challenge, dan laporan menyusul pada PBI berikutnya.</p>
</section>
@endsection
