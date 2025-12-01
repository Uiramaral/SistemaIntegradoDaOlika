@props(['title', 'value', 'subtitle', 'icon'])

<div class="card-metric card-hover">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="card-metric-title">{{ $title }}</p>
            <p class="card-metric-value">{{ $value }}</p>
            <p class="card-metric-subtitle">{{ $subtitle }}</p>
        </div>
        @if(isset($icon))
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                <i class="ph ph-{{ $icon }}"></i>
            </div>
        @endif
    </div>
</div>

