@extends('layouts.guest')

@section('title', 'Masuk')

@section('top-link')
    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full border border-current/20 px-4 py-2 text-sm font-semibold transition hover:bg-black/5 lg:text-eco-800 max-lg:hover:bg-white/10">
        Belum punya akun? <span class="underline underline-offset-4">Daftar</span>
    </a>
@endsection

@php
    // Bila login ditahan (lima kali gagal), ambil sisa detik dari pesan agar tombol bisa menghitung mundur.
    preg_match('/dalam (\d+) detik/', (string) $errors->first('email'), $m);
    $retry = (int) ($m[1] ?? 0);
@endphp

@section('content')
<div x-data="{
        busy: false,
        retry: {{ $retry }},
        init() {
            window.addEventListener('pageshow', () => this.busy = false);
            if (this.retry > 0) {
                const t = setInterval(() => { this.retry--; if (this.retry <= 0) clearInterval(t); }, 1000);
            }
        },
     }">

    <p class="mb-3 inline-flex items-center gap-2 text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">
        <span class="size-1.5 rounded-full bg-eco-500"></span> SELAMAT DATANG KEMBALI
    </p>
    <h1 class="text-[2rem] font-bold leading-[1.15] tracking-tight text-eco-900">Masuk.</h1>
    <p class="mt-2 text-[0.95rem] leading-relaxed text-slate-600">Masuk untuk melanjutkan challenge dan menjaga streak harianmu.</p>

    <form method="POST" action="{{ route('login') }}" novalidate class="mt-7 space-y-5" x-on:submit="busy = true">
        @csrf

        <x-ui.field name="email" type="email" label="Email" icon="mail" :value="old('email')"
                    required autofocus autocomplete="username" placeholder="nama@student.telkomuniversity.ac.id" />

        <x-ui.field name="password" type="password" label="Kata sandi" icon="lock"
                    required autocomplete="current-password" placeholder="Kata sandimu" />

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 select-none">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-eco-700 accent-eco-700 focus:ring-2 focus:ring-eco-300">
            Ingat saya di perangkat ini
        </label>

        <x-ui.button x-bind:disabled="busy || retry > 0">
            <span x-show="!busy && retry <= 0" class="inline-flex items-center gap-2">Masuk <x-ui.icon name="arrow-right" class="size-4" /></span>
            <span x-show="retry > 0" x-cloak class="inline-flex items-center gap-2">
                <x-ui.icon name="clock" class="size-4" /> Coba lagi dalam <span x-text="retry"></span> detik
            </span>
            <span x-show="busy && retry <= 0" x-cloak class="inline-flex items-center gap-2">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                Memeriksa…
            </span>
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-eco-800 underline underline-offset-4 hover:text-eco-600">Daftar</a>
    </p>
</div>
@endsection
