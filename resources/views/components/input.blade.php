@props(['placeholder' => '', 'value' => '', 'error' => null])

<input 
  {{ $attributes->merge([
    'class' => 'input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500' . ($error ? ' border-red-500' : ''),
    'value' => $value,
    'placeholder' => $placeholder
  ]) }}
/>
