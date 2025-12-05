@props(['title', 'value', 'subtitle', 'icon'])

<div class="rounded-lg border border-border bg-card p-6 shadow-sm transition hover:shadow-md" style="background-color: hsl(var(--card)); border-color: hsl(var(--border));">
  <div class="flex items-center justify-between">
    <div class="flex-1">
      <p class="text-sm mb-1" style="color: hsl(var(--muted-foreground));">{{ $title }}</p>
      <p class="text-2xl font-bold mb-1" style="color: hsl(var(--foreground));">{{ $value }}</p>
      <p class="text-xs" style="color: hsl(var(--muted-foreground));">{{ $subtitle }}</p>
    </div>
    @if(isset($icon))
      <div class="flex h-12 w-12 items-center justify-center rounded-lg" style="background-color: hsl(var(--primary) / 0.1); color: hsl(var(--primary));">
        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
      </div>
    @endif
  </div>
</div>
