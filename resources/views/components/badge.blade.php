@props([
  'color' => 'gray', // orange | gray | green | red | blue | purple
])

@php
$map = [
  'orange' => 'bg-orange-100 text-orange-700',
  'gray'   => 'bg-gray-100 text-gray-700',
  'green'  => 'bg-green-100 text-green-700',
  'red'    => 'bg-red-100 text-red-700',
  'blue'   => 'bg-blue-100 text-blue-700',
  'purple' => 'bg-purple-100 text-purple-700',
];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ".$map[$color]]) }}>
  {{ $slot }}
</span>