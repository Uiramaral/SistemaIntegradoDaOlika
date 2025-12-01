@props(['title' => null, 'actions' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'card p-5 shadow-sm']) }}>
    @if($title || $actions)
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            @if($title)
                <h3 class="text-gray-800 font-semibold text-base">{{ $title }}</h3>
            @endif
            @if($actions)
                <div>{{ $actions }}</div>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>

    @if($footer)
        <div class="mt-4 pt-4 border-t">
            {{ $footer }}
        </div>
    @endif
</div>

