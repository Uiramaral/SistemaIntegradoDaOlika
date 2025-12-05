@props(['items' => []])

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
  @foreach($items as $item)
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-4">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-sm font-medium text-muted-foreground">{{ $item['label'] ?? '' }}</p>
            <p class="text-2xl font-bold mt-1">{{ $item['value'] ?? '' }}</p>
          </div>
          @if(isset($item['icon']))
            <div class="ml-4 flex-shrink-0">
              <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 text-muted-foreground"></i>
            </div>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>

