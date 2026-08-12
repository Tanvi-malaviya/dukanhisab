@props([
    'type' => 'submit',
    'variant' => 'primary', // primary, secondary, success, danger
    'href' => null
])

@php
    $baseClasses = 'px-4 py-2 text-sm font-semibold rounded-xl transition-all cursor-pointer inline-flex items-center justify-center';
    $variants = [
        'primary' => 'bg-primary hover:bg-primary-hover text-white border border-transparent shadow-sm',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 shadow-xs',
        'success' => 'bg-success hover:bg-success-hover text-white border border-transparent shadow-sm',
        'danger' => 'bg-danger hover:bg-danger-hover text-white border border-transparent shadow-sm',
    ];
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
