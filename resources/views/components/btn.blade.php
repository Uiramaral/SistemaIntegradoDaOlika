@props([
  'variant' => 'primary', // primary | secondary | ghost | danger
  'size'    => 'md',      // sm | md | lg
  'block'   => false,     // true para width:100%
  'href'    => null,      // se passar href vira <a>
])

@php
$base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-1';
$sizes = [
  'sm' => 'text-sm px-3 py-1.5',
  'md' => 'text-sm px-4 py-2',
  'lg' => 'text-base px-5 py-2.5',
];
$variants = [
  'primary'   => 'bg-orange-500 text-white hover:bg-orange-600 focus:ring-orange-200',
  'secondary' => 'bg-white text-gray-800 border border-gray-200 hover:bg-gray-50 focus:ring-orange-200',
  'ghost'     => 'bg-transparent text-gray-700 hover:bg-gray-100 focus:ring-orange-200',
  'danger'    => 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-200',
];
$classes = implode(' ', [$base, $sizes[$size] ?? $sizes['md'], $variants[$variant] ?? $variants['primary'], $block ? 'w-full' : '']);
@endphp

@if($href)
  <a {{ $attributes->merge(['href' => $href, 'class' => $classes]) }}>
    {{ $slot }}
  </a>
@else
  <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
  </button>
@endif
