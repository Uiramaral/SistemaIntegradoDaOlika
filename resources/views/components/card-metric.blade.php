@props(['color' => 'gray', 'value' => '0', 'label' => ''])

@php
  $colors = [
    'orange' => 'text-orange-600',
    'green' => 'text-green-600',
    'blue' => 'text-blue-600',
    'purple' => 'text-purple-600',
    'gray' => 'text-gray-600',
  ];
@endphp

<div class="bg-white rounded-xl shadow p-6 text-center">
  <div class="text-3xl font-bold {{ $colors[$color] ?? $colors['gray'] }} mb-2">
    {{ $value }}
  </div>
  <div class="text-sm text-gray-600">
    {{ $label }}
  </div>
</div>
