@php
    // Menu hanya tampil bila route-nya sudah dibuat anggota lain.
    $menu = collect([
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Challenge', 'route' => 'challenges.index'],
        ['label' => 'Leaderboard', 'route' => 'leaderboard.index'],
        ['label' => 'Riwayat', 'route' => 'history.index'],
        ['label' => 'Profil', 'route' => 'profile.edit'],
    ])->filter(fn ($item) => Route::has($item['route']));
@endphp

<header x-data="{ open: false }" class="eco-nav bg-green-800 text-white shadow">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <a href="{{ url('/') }}" class="text-xl font-bold tracking-tight">
            eco<span class="font-normal">challenge</span>
        </a>

        <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi utama">
            @foreach ($menu as $item)
                <a href="{{ route($item['route']) }}"
                   class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs($item['route'].'*') ? 'bg-green-700 font-semibold' : 'hover:bg-green-700' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <button type="button" class="md:hidden p-2 rounded-lg hover:bg-green-700"
                x-on:click="open = !open" :aria-expanded="open" aria-controls="menu-ponsel"
                aria-label="Buka menu navigasi">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <nav id="menu-ponsel" x-show="open" x-cloak class="md:hidden border-t border-green-700 px-4 py-3 space-y-1" aria-label="Navigasi ponsel">
        @foreach ($menu as $item)
            <a href="{{ route($item['route']) }}" class="block px-3 py-2 rounded-lg hover:bg-green-700">{{ $item['label'] }}</a>
        @endforeach
    </nav>
</header>
