@props(['name', 'src' => null, 'seed' => null])
@php
    $words = collect(preg_split('/[\s._-]+/', trim((string) $name)))->filter();
    $initials = $words->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';

    // Warna lembut yang konsisten per orang (berdasarkan teks seed atau nama).
    $tints = [
        'bg-eco-100 text-eco-800',
        'bg-amber-100 text-amber-800',
        'bg-sky-100 text-sky-800',
        'bg-rose-100 text-rose-800',
        'bg-violet-100 text-violet-800',
        'bg-lime-100 text-lime-800',
    ];
    $tint = $tints[crc32((string) ($seed ?? $name)) % count($tints)];

    // Nilai bawaan dilepas bila pemanggil menimpanya, agar tidak ada dua utilitas yang bertabrakan.
    $given = ' '.(string) $attributes->get('class').' ';
    $defaults = [
        'size-10' => ! preg_match('/\s(?:[a-z0-9-]+:)*size-/', $given),
        'text-sm' => ! preg_match('/\s(?:[a-z0-9-]+:)*text-(?:xs|sm|base|lg|xl|\dxl)\s/', $given),
        'ring-1 ring-black/5' => ! preg_match('/\s(?:[a-z0-9-]+:)*ring(?:-\d)?\s|\sring-\d/', $given),
    ];
@endphp
<span {{ $attributes->class(array_merge(['relative inline-grid shrink-0 place-items-center overflow-hidden rounded-full font-bold', $tint], $defaults)) }}>
    @if ($src)
        <img src="{{ $src }}" alt="" class="size-full object-cover" loading="lazy">
    @else
        <span aria-hidden="true">{{ $initials }}</span>
    @endif
</span>
