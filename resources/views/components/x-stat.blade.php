@props(['title', 'value', 'icon' => null, 'description' => null])

<div class="stat-card">
    <div class="stat-card-content">
        <h3>{{ $title }}</h3>
        <div class="value">{{ $value }}</div>
        @if($description)
            <div class="description">{{ $description }}</div>
        @endif
    </div>
    @if($icon)
        <div class="stat-card-icon">
            <i data-lucide="{{ $icon }}" class="h-6 w-6"></i>
        </div>
    @endif
</div>

<script>
    if (window.lucide) {
        window.lucide.createIcons();
    }
</script>

