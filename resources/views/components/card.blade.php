{{-- Componente Card --}}
<div class="card {{ $class ?? '' }}">
    @if(isset($title))
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            @if(isset($actions))
                <div class="flex space-x-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    
    {{ $slot }}
</div>