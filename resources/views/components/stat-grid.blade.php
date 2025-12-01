@props(['items' => []])

<div class="stat-grid">
  @foreach($items as $item)
    <div class="stat-card">
      <div class="flex items-center justify-between w-full">
        <div class="flex-1">
          <div class="value">{{ $item['value'] ?? '' }}</div>
          <div class="label">{{ $item['label'] ?? '' }}</div>
        </div>
        @if(isset($item['icon']))
          <div class="ml-4 flex-shrink-0">
            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 text-gray-400"></i>
          </div>
        @endif
      </div>
    </div>
  @endforeach
</div>

