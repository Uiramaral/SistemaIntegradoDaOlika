@extends('layouts.admin')

@section('title', 'Fidelidade')
@section('page_title', 'Fidelidade')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar clientes..." class="input" id="search-loyalty">
        </div>
        <div class="flex gap-2">
            <select class="input" id="points-filter">
                <option value="">Todos os pontos</option>
                <option value="high">Alto (500+)</option>
                <option value="medium">Médio (100-499)</option>
                <option value="low">Baixo (0-99)</option>
            </select>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <x-card class="text-center">
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ $totalPoints ?? 0 }}</div>
        <div class="text-sm text-gray-600">Total de Pontos</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-green-600 mb-2">{{ $activeMembers ?? 0 }}</div>
        <div class="text-sm text-gray-600">Membros Ativos</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-purple-600 mb-2">{{ $redeemedPoints ?? 0 }}</div>
        <div class="text-sm text-gray-600">Pontos Resgatados</div>
    </x-card>
    
    <x-card class="text-center">
        <div class="text-3xl font-bold text-orange-600 mb-2">{{ $averagePoints ?? 0 }}</div>
        <div class="text-sm text-gray-600">Média por Cliente</div>
    </x-card>
</div>

<x-card title="Programa de Fidelidade">
    <x-table :headers="['Cliente', 'Pontos Atuais', 'Nível', 'Última Atividade', 'Ações']" :actions="false">
        @forelse($loyalties ?? [] as $loyalty)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-full mr-3 flex items-center justify-center">
                            <i class="fas fa-star text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">{{ $loyalty->customer->nome ?? 'Cliente' }}</div>
                            <div class="text-sm text-gray-500">{{ $loyalty->customer->email ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 border-b">
                    <span class="text-2xl font-bold text-blue-600">{{ $loyalty->pontos ?? 0 }}</span>
                </td>
                <td class="px-4 py-3 border-b">
                    @php
                        $points = $loyalty->pontos ?? 0;
                        $level = $points >= 1000 ? 'gold' : ($points >= 500 ? 'silver' : 'bronze');
                        $levelClass = match($level) {
                            'gold' => 'badge-warning',
                            'silver' => 'badge-info',
                            'bronze' => 'badge-secondary',
                            default => 'badge-secondary'
                        };
                        $levelName = match($level) {
                            'gold' => 'Ouro',
                            'silver' => 'Prata',
                            'bronze' => 'Bronze',
                            default => 'Bronze'
                        };
                    @endphp
                    <span class="badge {{ $levelClass }}">{{ $levelName }}</span>
                </td>
                <td class="px-4 py-3 border-b">
                    @if($loyalty->last_activity ?? false)
                        {{ \Carbon\Carbon::parse($loyalty->last_activity)->format('d/m/Y') }}
                    @else
                        <span class="text-gray-400">Nunca</span>
                    @endif
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <div class="flex gap-2 justify-end">
                        <x-button href="/loyalty/{{ $loyalty->id }}" variant="secondary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        <x-button href="/loyalty/{{ $loyalty->id }}/edit" variant="primary" size="sm">
                            <i class="fas fa-edit"></i>
                        </x-button>
                        <x-button href="/loyalty/{{ $loyalty->id }}/redeem" variant="success" size="sm">
                            <i class="fas fa-gift"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                    Nenhum registro de fidelidade encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>

<x-card title="Configurações do Programa" class="mt-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pontos por Real</label>
            <input type="number" class="input" value="{{ $settings['points_per_real'] ?? 1 }}" placeholder="Ex: 1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pontos para Resgate</label>
            <input type="number" class="input" value="{{ $settings['points_for_redeem'] ?? 100 }}" placeholder="Ex: 100">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor do Resgate</label>
            <input type="number" class="input" value="{{ $settings['redeem_value'] ?? 10 }}" placeholder="Ex: 10">
        </div>
    </div>
    <div class="mt-4">
        <x-button variant="primary">
            <i class="fas fa-save"></i> Salvar Configurações
        </x-button>
    </div>
</x-card>

@push('scripts')
<script>
    // Filtros dinâmicos
    document.getElementById('search-loyalty').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endpush
@endsection