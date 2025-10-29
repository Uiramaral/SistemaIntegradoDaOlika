{{-- Componente Button --}}
@php
    $baseClasses = 'btn';
    $variantClasses = match($variant ?? 'primary') {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'success' => 'btn-success',
        'danger' => 'btn-danger',
        default => 'btn-primary'
    };
    $sizeClasses = match($size ?? 'md') {
        'sm' => 'px-3 py-1 text-sm',
        'md' => 'px-4 py-2',
        'lg' => 'px-6 py-3 text-lg',
        default => 'px-4 py-2'
    };
    $allClasses = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses . ' ' . ($class ?? '');
@endphp

@if(isset($href))
    <a href="{{ $href }}" class="{{ $allClasses }}" {{ $attributes ?? '' }}>
        @if(isset($icon))
            <i class="fas {{ $icon }} mr-2"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type ?? 'button' }}" class="{{ $allClasses }}" {{ $attributes ?? '' }}>
        @if(isset($icon))
            <i class="fas {{ $icon }} mr-2"></i>
        @endif
        {{ $slot }}
    </button>
@endif
