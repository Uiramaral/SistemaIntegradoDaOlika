@props(['label'=>null,'hint'=>null])
<label class="block">
  @if($label)<span class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</span>@endif
  {{ $slot }}
  @if($hint)<span class="block text-xs text-gray-400 mt-1">{{ $hint }}</span>@endif
</label>
