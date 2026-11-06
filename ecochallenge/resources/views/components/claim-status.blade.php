@props(['status'])

@php
    $map = [
        'pending' => ['label' => 'Menunggu verifikasi', 'class' => 'bg-amber-100 text-amber-800'],
        'approved' => ['label' => 'Diterima', 'class' => 'bg-green-100 text-green-800'],
        'rejected' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-700'],
    ];
    $state = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-slate-100 text-slate-600'];
@endphp

<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $state['class'] }}">
    {{ $state['label'] }}
</span>