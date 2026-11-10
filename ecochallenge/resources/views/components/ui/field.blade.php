@props([
    'name',
    'label',
    'type' => 'text',
    'icon' => null,
    'hint' => null,
    'value' => null,
])
@php
    $hasError = $errors->has($name);
    $isPassword = $type === 'password';
@endphp
<div class="space-y-1.5" @if ($isPassword) x-data="{ show: false }" @endif>
    <label for="{{ $name }}" class="block text-sm font-semibold text-eco-900">{{ $label }}</label>

    <div class="group relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-3.5 grid place-items-center text-slate-400 transition group-focus-within:text-eco-700">
                <x-ui.icon :name="$icon" class="size-[1.15rem]" />
            </span>
        @endif

        <input
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($isPassword) x-bind:type="show ? 'text' : 'password'" type="password" @else type="{{ $type }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {{ $attributes->class([
                'w-full rounded-xl border bg-white py-3 text-[0.95rem] text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4',
                $icon ? 'pl-11' : 'pl-4',
                $isPassword ? 'pr-12' : 'pr-4',
                $hasError
                    ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-100'
                    : 'border-slate-200 hover:border-slate-300 focus:border-eco-600 focus:ring-eco-100',
            ]) }}
        >

        @if ($isPassword)
            <button type="button" x-on:click="show = !show" :aria-pressed="show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    class="absolute inset-y-0 right-1.5 my-1.5 grid w-10 place-items-center rounded-lg text-slate-400 outline-none transition hover:bg-slate-100 hover:text-eco-800 focus-visible:ring-2 focus-visible:ring-eco-400">
                <x-ui.icon name="eye" class="size-[1.15rem]" x-show="!show" />
                <x-ui.icon name="eye-off" class="size-[1.15rem]" x-show="show" x-cloak />
            </button>
        @endif
    </div>

    @error($name)
        <p class="flex items-start gap-1.5 text-sm font-medium text-rose-600" id="{{ $name }}-error" role="alert">
            <x-ui.icon name="circle-alert" class="mt-0.5 size-4" /> <span>{{ $message }}</span>
        </p>
    @else
        @if ($hint)<p class="text-xs text-slate-500">{{ $hint }}</p>@endif
    @enderror

    {{ $slot }}
</div>
