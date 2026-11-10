@props(['name', 'stroke' => 1.8])
@php
    // Set ikon dibuat dari Lucide (lihat resources/icons.php). Dibaca sekali per proses.
    static $icons = null;
    $icons ??= require resource_path('icons.php');

    // Ukuran bawaan hanya dipakai bila pemanggil tidak menentukan size-*; dua utilitas size
    // pada satu elemen akan bertabrakan dan yang menang ditentukan urutan CSS, bukan urutan kelas.
    $hasSize = preg_match('/(^|\s)(?:[a-z0-9-]+:)*size-/', (string) $attributes->get('class'));
@endphp
<svg {{ $attributes->class(['size-5' => ! $hasSize, 'shrink-0']) }}xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $icons[$name] ?? '' !!}</svg>
