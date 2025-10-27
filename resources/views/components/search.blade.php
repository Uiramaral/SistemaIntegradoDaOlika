<div class="search">
  <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="#9ca3af" stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
  <input type="search" name="{{ $name ?? 'q' }}" placeholder="{{ $placeholder ?? 'Buscar...' }}" value="{{ request($name ?? 'q') }}">
</div>
