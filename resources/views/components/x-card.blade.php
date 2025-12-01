@props(['title' => '', 'footer' => false])

<div class="card-slim">
  @if ($title)
    <h3 class="font-semibold text-sm mb-2">{{ $title }}</h3>
  @endif

  <div class="flex-1">
    {{ $slot }}
  </div>

  @if ($footer)
    <div class="mt-3 pt-2 border-t">
      {{ $footer }}
    </div>
  @endif
</div>

