@props([
  'href'   => null,
  'label'  => 'Abrir no Maps',
  'mode'   => 'full',
  'size'   => 'md',
  'title'  => null,
])

@php
  $base = 'pill inline-flex items-center gap-1';
  $sz   = $size === 'sm' ? 'px-2 py-[3px] text-xs' : '';
  $tt   = $title ?? 'Abrir no Google Maps';

  $icon = <<<'SVG'
<svg class="icon-14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
  <path d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7Z" stroke="currentColor" stroke-width="1.5"/>
  <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.5"/>
</svg>
SVG;
@endphp

@if($href)
  <a href="{{ $href }}" target="_blank" rel="noopener"
     title="{{ $tt }}"
     {{ $attributes->merge(['class' => trim($base.' '.$sz)]) }}>
    @if($mode === 'icon')
      {!! $icon !!}
      <span class="sr-only">{{ $label }}</span>
    @elseif($mode === 'compact')
      {!! $icon !!}
      <span>Mapa</span>
    @else {{-- full --}}
      {!! $icon !!}
      <span>{{ $label }}</span>
    @endif
  </a>
@else
  <span class="pill {{ $sz }}" title="Sem endereço">
    {!! $icon !!}<span>@if($mode==='full') {{ $label }} @else Mapa @endif</span>
  </span>
@endif
