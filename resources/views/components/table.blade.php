{{-- Componente Table --}}
<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50">
                @foreach($headers as $header)
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b">{{ $header }}</th>
                @endforeach
                @if(isset($actions) && $actions)
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 border-b text-right">Ações</th>
                @endif
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
    
    @if(isset($pagination))
        <div class="mt-4">
            {{ $pagination }}
        </div>
    @endif
</div>
