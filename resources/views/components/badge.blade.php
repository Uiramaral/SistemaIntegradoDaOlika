@props(['type' => 'info'])

@php
  $classes = [
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'danger'  => 'bg-red-100 text-red-800',
    'info'    => 'bg-blue-100 text-blue-800',
    'gray'    => 'bg-gray-100 text-gray-800',
  ];
@endphp

<span class="px-2 py-1 text-xs font-medium rounded-full {{ $classes[$type] ?? $classes['info'] }}">
  {{ $slot }}
</span>