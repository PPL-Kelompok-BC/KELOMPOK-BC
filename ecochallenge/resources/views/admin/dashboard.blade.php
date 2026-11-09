@extends('layouts.auth')
@section('title', 'Dashboard Admin')
@section('nav')
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">Keluar</button></form>
@endsection
@section('content')
<section class="auth-card panel">
<div class="eyebrow">DASHBOARD ADMIN</div>
<h1>Halo, {{ auth()->user()->name }}.</h1>
<p class="auth-lead">Kamu masuk sebagai <strong>admin</strong>. Tinjau klaim mahasiswa atau kelola challenge.</p>
<div class="grid sm:grid-cols-2 gap-3 mt-4">
<a class="btn" href="{{ route('admin.claims.index') }}">Tinjau Klaim</a>
<a class="btn" href="{{ route('admin.challenges.index') }}">Kelola Challenge</a>
</div>
</section>
@endsection
