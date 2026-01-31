@props(['title', 'value', 'subtitle', 'icon'])

<div class="card p-6 flex flex-col justify-between">
  <div class="flex items-center justify-between">
    <div class="flex-1">
      <p class="text-sm mb-1" style="color: var(--muted);">{{ $title }}</p>
      <p class="text-2xl font-bold mb-1" style="color: var(--text);">{{ $value }}</p>
      <p class="text-xs" style="color: var(--muted);">{{ $subtitle }}</p>
    </div>
    @if(isset($icon))
      <div class="flex h-12 w-12 items-center justify-center rounded-lg"
        style="background-color: rgba(249, 115, 22, 0.15); color: var(--primary);">
        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
      </div>
    @endif
  </div>
</div>