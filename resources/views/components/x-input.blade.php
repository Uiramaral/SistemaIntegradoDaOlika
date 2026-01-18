@props(['type' => 'text', 'placeholder' => '', 'value' => '', 'name' => ''])

<input
  type="{{ $type }}"
  name="{{ $name }}"
  value="{{ $value }}"
  placeholder="{{ $placeholder }}"
  {{ $attributes->merge(['class' => 'w-full border border-gray-300 rounded-md px-3 py-2 h-10 text-sm focus:ring-orange-500 focus:border-orange-500']) }}
>

