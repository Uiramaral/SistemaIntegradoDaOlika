@extends('layouts.dashboard')

@section('title', 'Health Check')

@section('content')
<div class="page p-6">
    <h1 class="text-2xl font-bold mb-4">Status do Sistema</h1>
    <p class="text-gray-600 mb-6">Última verificação: {{ now()->format('d/m/Y H:i:s') }}</p>

    <!-- Status e Botão -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
            <div class="w-3 h-3 rounded-full mr-2 
                {{ $health['status'] === 'healthy' ? 'bg-green-500' : 
                   ($health['status'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}">
            </div>
            <span class="font-semibold text-lg
                {{ $health['status'] === 'healthy' ? 'text-green-600' : 
                   ($health['status'] === 'warning' ? 'text-yellow-600' : 'text-red-600') }}">
                {{ ucfirst($health['status']) }}
            </span>
        </div>
        <button onclick="refreshHealthCheck()" 
                class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
            <i class="fas fa-sync-alt mr-2"></i>
            Atualizar
        </button>
    </div>

    <!-- Checks Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($health['checks'] as $checkName => $check)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 capitalize">
                    {{ str_replace('_', ' ', $checkName) }}
                </h3>
                <div class="w-3 h-3 rounded-full
                    {{ $check['status'] === 'healthy' ? 'bg-green-500' : 
                       ($check['status'] === 'warning' ? 'bg-yellow-500' : 'bg-red-500') }}">
                </div>
            </div>
            
            <p class="text-gray-600 mb-4">{{ $check['message'] }}</p>
            
            @if(isset($check['stats']))
            <div class="space-y-2">
                @foreach($check['stats'] as $statName => $statValue)
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $statName) }}:</span>
                    <span class="text-sm font-medium">{{ $statValue }}</span>
                </div>
                @endforeach
            </div>
            @endif
            
            @if(isset($check['execution_time_ms']))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Tempo de execução:</span>
                    <span class="text-sm font-medium">{{ $check['execution_time_ms'] }}ms</span>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Auto-refresh Info -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
            <div>
                <h4 class="font-semibold text-blue-900">Atualização Automática</h4>
                <p class="text-blue-800 text-sm">
                    Esta página é atualizada automaticamente a cada 30 segundos.
                    Para atualizações manuais, clique no botão "Atualizar".
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh a cada 30 segundos
    setInterval(function() {
        refreshHealthCheck();
    }, 30000);

    function refreshHealthCheck() {
        fetch('{{ route("admin.health") }}')
            .then(response => response.text())
            .then(html => {
                // Atualizar apenas o conteúdo principal
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('.page');
                if (newContent) {
                    document.querySelector('.page').innerHTML = newContent.innerHTML;
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar health check:', error);
            });
    }
</script>
@endpush
