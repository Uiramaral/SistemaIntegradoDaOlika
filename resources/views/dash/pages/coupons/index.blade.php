@extends('layouts.admin')

@section('title', 'Cupons')
@section('page_title', 'Cupons')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" placeholder="Buscar cupons..." class="input" id="search-coupons">
        </div>
        <div class="flex gap-2">
            <select class="input" id="status-filter">
                <option value="">Todos os status</option>
                <option value="active">Ativos</option>
                <option value="inactive">Inativos</option>
                <option value="expired">Expirados</option>
            </select>
            <x-button href="/coupons/create" variant="primary">
                <i class="fas fa-plus"></i> Novo Cupom
            </x-button>
        </div>
    </div>
</div>

<x-card title="Lista de Cupons">
    <x-table :headers="['Código', 'Descrição', 'Desconto', 'Validade', 'Usos', 'Status', 'Ações']" :actions="false">
        @forelse($coupons ?? [] as $coupon)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 border-b">
                    <div class="font-mono font-medium text-orange-600">{{ $coupon->code ?? '—' }}</div>
                </td>
                <td class="px-4 py-3 border-b">{{ Str::limit($coupon->description ?? 'Sem descrição', 40) }}</td>
                <td class="px-4 py-3 border-b">
                    <span class="font-medium text-green-600">
                        @if($coupon->type === 'percentage')
                            {{ $coupon->discount ?? 0 }}%
                        @else
                            R$ {{ number_format($coupon->discount ?? 0, 2, ',', '.') }}
                        @endif
                    </span>
                </td>
                <td class="px-4 py-3 border-b">
                    @if($coupon->expires_at ?? false)
                        {{ \Carbon\Carbon::parse($coupon->expires_at)->format('d/m/Y') }}
                    @else
                        <span class="text-gray-400">Sem validade</span>
                    @endif
                </td>
                <td class="px-4 py-3 border-b">
                    <span class="badge badge-info">{{ $coupon->usage_count ?? 0 }}/{{ $coupon->usage_limit ?? '∞' }}</span>
                </td>
                <td class="px-4 py-3 border-b">
                    @php
                        $isExpired = $coupon->expires_at && \Carbon\Carbon::parse($coupon->expires_at)->isPast();
                        $isActive = ($coupon->active ?? false) && !$isExpired;
                    @endphp
                    <span class="badge {{ $isActive ? 'badge-success' : ($isExpired ? 'badge-danger' : 'badge-warning') }}">
                        {{ $isActive ? 'Ativo' : ($isExpired ? 'Expirado' : 'Inativo') }}
                    </span>
                </td>
                <td class="px-4 py-3 border-b text-right">
                    <div class="flex gap-2 justify-end">
                        <x-button href="/coupons/{{ $coupon->id }}" variant="secondary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        <x-button href="/coupons/{{ $coupon->id }}/edit" variant="primary" size="sm">
                            <i class="fas fa-edit"></i>
                        </x-button>
                        <x-button variant="danger" size="sm" onclick="confirmDelete({{ $coupon->id }})">
                            <i class="fas fa-trash"></i>
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    Nenhum cupom encontrado
                </td>
            </tr>
        @endforelse
    </x-table>
</x-card>

@push('scripts')
<script>
    function confirmDelete(couponId) {
        if (confirm('Tem certeza que deseja excluir este cupom?')) {
            fetch(`/coupons/${couponId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                location.reload();
            });
        }
    }
    
    // Filtros dinâmicos
    document.getElementById('search-coupons').addEventListener('input', function() {
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