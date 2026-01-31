@props(['title', 'subtitle'])

<div class="card p-6 h-full">
    <div class="mb-6">
        <h3 class="text-xl font-bold" style="color: var(--text);">{{ $title }}</h3>
        @if(isset($subtitle))
            <p class="text-sm mt-1" style="color: var(--muted);">{{ $subtitle }}</p>
        @endif
    </div>
    <div>
        {{ $slot }}
    </div>
</div>