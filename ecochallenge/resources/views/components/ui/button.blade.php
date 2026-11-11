@props(['type' => 'submit'])
<button type="{{ $type }}" {{ $attributes->class([
    'inline-flex w-full items-center justify-center gap-2 rounded-xl bg-eco-800 px-5 py-3.5 text-[0.95rem] font-semibold text-white shadow-lg shadow-eco-900/20 outline-none',
    'transition hover:-translate-y-0.5 hover:bg-eco-700 hover:shadow-xl active:translate-y-0 focus-visible:ring-4 focus-visible:ring-eco-200',
    'disabled:pointer-events-none disabled:opacity-60',
]) }}>{{ $slot }}</button>
