@props(['label' => '', 'required' => false, 'error' => null])

<div class="mb-4">
  @if($label)
    <label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 mb-2']) }}>
      {{ $label }}
      @if($required)
        <span class="text-red-500">*</span>
      @endif
    </label>
  @endif
  
  {{ $slot }}
  
  @if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
  @endif
</div>
