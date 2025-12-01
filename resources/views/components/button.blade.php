@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
  $variants = [
    'primary' => 'bg-orange-600 hover:bg-orange-700 text-white',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
    'success' => 'bg-green-600 hover:bg-green-700 text-white',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white',
    'info' => 'bg-blue-600 hover:bg-blue-700 text-white',
    'outline' => 'border border-gray-300 hover:bg-gray-50 text-gray-700',
  ];
  
  $sizes = [
    'sm' => 'px-3 py-1.5 text-sm h-8',
    'md' => 'px-4 py-2.5 text-base h-10',
    'lg' => 'px-6 py-3 text-lg h-12',
  ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}>
  {{ $slot }}
</button>