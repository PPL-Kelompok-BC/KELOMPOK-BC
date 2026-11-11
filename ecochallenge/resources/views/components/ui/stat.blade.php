@props(['icon', 'label', 'value', 'hint' => null, 'tone' => 'green'])
@php
    $tones = [
        'green' => 'bg-eco-100 text-eco-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'violet' => 'bg-violet-100 text-violet-700',
    ];
@endphp
<div {{ $attributes->class(['group relative overflow-hidden rounded-2xl bg-white p-4 shadow-soft ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-lift sm:p-5']) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
        <span class="grid size-9 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['green'] }}">
            <x-ui.icon :name="$icon" class="size-[1.15rem]" />
        </span>
    </div>
    <p class="mt-3 text-3xl font-bold tracking-tight text-eco-900 tabular-nums">{{ $value }}</p>
    @if ($hint)<p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>@endif
</div>
