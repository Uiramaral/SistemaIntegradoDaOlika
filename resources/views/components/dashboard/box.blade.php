@props(['title', 'subtitle'])

<div class="box card-hover">
    <h3 class="box-title">{{ $title }}</h3>
    @if(isset($subtitle))
        <p class="box-subtitle">{{ $subtitle }}</p>
    @endif
    {{ $slot }}
</div>

