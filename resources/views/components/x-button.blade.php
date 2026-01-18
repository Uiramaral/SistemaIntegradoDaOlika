@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
  $base = 'inline-flex items-center justify-center font-medium rounded-md transition-all duration-150';
  $variants = [
    'primary' => 'bg-orange-500 text-white hover:bg-orange-600',
    'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    'outline' => 'border border-gray-300 text-gray-700 hover:bg-gray-100',
  ];
  $sizes = [
    'sm' => 'text-sm h-8 px-3',
    'md' => 'text-base h-10 px-4',
    'lg' => 'text-lg h-12 px-6',
  ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base {$variants[$variant]} {$sizes[$size]}"]) }}>
  {{ $slot }}
</button>

