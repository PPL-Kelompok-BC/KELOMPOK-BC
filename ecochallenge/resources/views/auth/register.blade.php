@extends('layouts.guest')

@section('title', 'Daftar')

@section('top-link')
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full border border-current/20 px-4 py-2 text-sm font-semibold transition hover:bg-black/5 lg:text-eco-800 max-lg:hover:bg-white/10">
        Sudah punya akun? <span class="underline underline-offset-4">Masuk</span>
    </a>
@endsection

@section('content')
<div x-data="{
        username: @js(old('username', '')),
        password: '',
        confirm: '',
        busy: false,
        get cleanUsername() { return this.username.trim().toLowerCase(); },
        get score() {
            const p = this.password;
            let s = 0;
            if (p.length >= 8) s++;
            if (p.length >= 12) s++;
            if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
            if (/\d/.test(p) && /[^A-Za-z0-9]/.test(p)) s++;
            return p.length === 0 ? 0 : Math.max(1, s);
        },
        get label() { return ['', 'Lemah', 'Cukup', 'Baik', 'Kuat'][this.score]; },
        get matches() { return this.confirm.length > 0 && this.confirm === this.password; },
     }"
     x-init="window.addEventListener('pageshow', () => busy = false)">

    <p class="mb-3 inline-flex items-center gap-2 text-[0.7rem] font-bold tracking-[0.2em] text-eco-700">
        <span class="size-1.5 rounded-full bg-eco-500"></span> MULAI DARI SINI
    </p>
    <h1 class="text-[2rem] font-bold leading-[1.15] tracking-tight text-eco-900">Buat akunmu.</h1>
    <p class="mt-2 text-[0.95rem] leading-relaxed text-slate-600">
        Pilih username untuk nama tampilanmu di leaderboard. Nama aslimu tidak ditampilkan di sana.
    </p>

    <form method="POST" action="{{ route('register') }}" novalidate class="mt-7 space-y-5" x-on:submit="busy = true">
        @csrf

        <x-ui.field name="name" label="Nama lengkap" icon="user-round" :value="old('name')"
                    maxlength="255" required autofocus autocomplete="name" placeholder="Nama sesuai KTM" />

        <x-ui.field name="username" label="Username" icon="at-sign" :value="old('username')"
                    hint="Huruf kecil, angka, titik, dan garis bawah. 3–30 karakter."
                    minlength="3" maxlength="30" required autocomplete="username" autocapitalize="none" spellcheck="false"
                    placeholder="contoh: rani.hijau" x-model="username">
            <p x-show="cleanUsername.length >= 3" x-cloak class="flex items-center gap-1.5 text-xs text-eco-700">
                <x-ui.icon name="trophy" class="size-3.5" />
                Di leaderboard kamu tampil sebagai <strong class="font-semibold" x-text="'@' + cleanUsername"></strong>
            </p>
        </x-ui.field>

        <x-ui.field name="email" type="email" label="Email" icon="mail" :value="old('email')"
                    maxlength="255" required autocomplete="email" placeholder="nama@student.telkomuniversity.ac.id" />

        <x-ui.field name="password" type="password" label="Kata sandi" icon="lock"
                    hint="Minimal 8 karakter." minlength="8" required autocomplete="new-password"
                    placeholder="Buat kata sandi" x-model="password">
            <div x-show="password.length" x-cloak class="pt-1" aria-live="polite">
                <div class="flex gap-1.5">
                    <template x-for="i in 4" :key="i">
                        <span class="h-1.5 flex-1 rounded-full transition-colors duration-300"
                              :class="i <= score ? (score <= 1 ? 'bg-rose-400' : score === 2 ? 'bg-amber-400' : 'bg-eco-500') : 'bg-slate-200'"></span>
                    </template>
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Kekuatan kata sandi: <strong class="font-semibold text-slate-700" x-text="label"></strong></p>
            </div>
        </x-ui.field>

        <x-ui.field name="password_confirmation" type="password" label="Ulangi kata sandi" icon="lock"
                    required autocomplete="new-password" placeholder="Ketik ulang kata sandi" x-model="confirm">
            <p x-show="confirm.length" x-cloak class="flex items-center gap-1.5 text-xs" :class="matches ? 'text-eco-700' : 'text-slate-500'">
                <x-ui.icon name="circle-check" class="size-3.5" x-show="matches" />
                <span x-text="matches ? 'Kata sandi cocok' : 'Kata sandi belum sama'"></span>
            </p>
        </x-ui.field>

        <x-ui.button x-bind:disabled="busy">
            <span x-show="!busy" class="inline-flex items-center gap-2">Daftar <x-ui.icon name="arrow-right" class="size-4" /></span>
            <span x-show="busy" x-cloak class="inline-flex items-center gap-2">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                Membuat akun…
            </span>
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-eco-800 underline underline-offset-4 hover:text-eco-600">Masuk</a>
    </p>
</div>
@endsection
