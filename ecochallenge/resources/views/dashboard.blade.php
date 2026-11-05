@extends('layouts.auth')
@section('title', 'Dashboard')
@section('content')
<section class="auth-card panel">
<div class="eyebrow">DASHBOARD MAHASISWA</div>
<h1>Halo, {{ auth()->user()->name }}.</h1>
<p class="auth-lead">Kamu masuk sebagai <strong>{{ auth()->user()->username }}</strong>. Fitur dashboard menyusul pada PBI berikutnya.</p>
</section>
@endsection
