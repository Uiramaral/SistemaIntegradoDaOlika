@props(['title' => null, 'actions' => null, 'footer' => null])

<div class="card">
    @if($title || $actions)
        <div class="card-header">
            @if($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @if($actions)
                <div class="card-actions">{{ $actions }}</div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>

