@extends('dashboard.layouts.app')

@section('title', 'Taxas de Entrega por Distância')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Taxas de Entrega</h1>
        <p class="text-muted-foreground">Configure faixas dinâmicas de distância e taxas de entrega</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Simulador de Taxa de Entrega -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Simulador de Taxa</h3>
                <p class="text-sm text-muted-foreground">Teste o cálculo de taxa de entrega por CEP</p>
            </div>
            <div class="p-6 pt-0 space-y-4">
                <form id="simulateForm" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="simulate_zipcode">CEP de Destino</label>
                        <input 
                            type="text" 
                            id="simulate_zipcode" 
                            name="zipcode" 
                            placeholder="00000-000" 
                            maxlength="10"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            required
                        >
                        <p class="text-xs text-muted-foreground">Digite o CEP sem traço ou com traço</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="simulate_subtotal">Valor do Pedido (R$)</label>
                        <input 
                            type="number" 
                            id="simulate_subtotal" 
                            name="subtotal" 
                            step="0.01" 
                            min="0" 
                            value="0" 
                            placeholder="0.00"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                        <p class="text-xs text-muted-foreground">Subtotal do carrinho (opcional, para aplicar descontos progressivos)</p>
                    </div>
                    <button 
                        type="submit" 
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full"
                    >
                        Calcular Taxa
                    </button>
                </form>

                <div id="simulationResult" class="hidden mt-4 p-4 rounded-lg border bg-muted">
                    <h4 class="font-semibold mb-2">Resultado da Simulação</h4>
                    <div class="space-y-1 text-sm">
                        <div><span class="font-medium">Distância:</span> <span id="result_distance">-</span> km</div>
                        <div><span class="font-medium">Taxa Base:</span> <span id="result_base_fee">R$ 0,00</span></div>
                        <div id="result_discount_row" class="hidden">
                            <div><span class="font-medium">Desconto:</span> <span id="result_discount_percent">0%</span> (<span id="result_discount_amount">R$ 0,00</span>)</div>
                        </div>
                        <div class="pt-2 border-t font-semibold">
                            <span class="font-medium">Taxa Final:</span> <span id="result_final_fee" class="text-primary">R$ 0,00</span>
                        </div>
                        <div id="result_free" class="hidden text-green-600 font-semibold mt-2">
                            ✓ Entrega Grátis
                        </div>
                    </div>
                </div>

                <div id="simulationError" class="hidden mt-4 p-4 rounded-lg border bg-red-50 text-red-900 text-sm"></div>
            </div>
        </div>

        <!-- Formulário de Nova Faixa -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Nova Faixa de Distância</h3>
                <p class="text-sm text-muted-foreground">Adicione uma nova faixa de distância e taxa</p>
            </div>
            <form action="{{ route('dashboard.delivery-pricing.store') }}" method="POST" class="p-6 pt-0 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="min_km">Distância Mínima (km)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="min_km" 
                            id="min_km"
                            placeholder="0.00"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" 
                            required
                        >
                        @error('min_km')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="max_km">Distância Máxima (km)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="max_km" 
                            id="max_km"
                            placeholder="999.99"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" 
                            required
                        >
                        @error('max_km')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="fee">Taxa de Entrega (R$)</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        name="fee" 
                        id="fee"
                        placeholder="0.00"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" 
                        required
                    >
                    <p class="text-xs text-muted-foreground">Valor fixo ou valor por km (se a faixa for grande, será calculado por km)</p>
                    @error('fee')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="min_amount_free">Frete Grátis a partir de (R$)</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        name="min_amount_free" 
                        id="min_amount_free"
                        placeholder="Opcional"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                    <p class="text-xs text-muted-foreground">Se preenchido, a entrega será grátis quando o pedido atingir este valor nesta faixa</p>
                    @error('min_amount_free')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium" for="sort_order">Ordem</label>
                        <input 
                            type="number" 
                            min="0" 
                            name="sort_order" 
                            id="sort_order"
                            placeholder="0"
                            value="{{ old('sort_order', 0) }}"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                        <p class="text-xs text-muted-foreground">Ordem de prioridade (menor número = maior prioridade)</p>
                        @error('sort_order')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer pt-8">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4">
                            <span class="text-sm font-medium">Faixa Ativa</span>
                        </label>
                    </div>
                </div>
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full"
                >
                    Adicionar Faixa
                </button>
            </form>
        </div>
    </div>

    <!-- Tabela de Faixas Existentes -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-2xl font-semibold leading-none tracking-tight">Faixas de Distância Configuradas</h3>
            <p class="text-sm text-muted-foreground">Gerencie as faixas de distância e taxas de entrega</p>
        </div>
        <div class="p-6 pt-0">
            @if($rows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-3 text-sm font-medium">Distância (km)</th>
                                <th class="text-left p-3 text-sm font-medium">Taxa (R$)</th>
                                <th class="text-left p-3 text-sm font-medium">Frete Grátis a partir de</th>
                                <th class="text-left p-3 text-sm font-medium">Ordem</th>
                                <th class="text-left p-3 text-sm font-medium">Status</th>
                                <th class="text-right p-3 text-sm font-medium">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr class="border-b hover:bg-muted/50" data-row-id="{{ $row->id }}">
                                    <td class="p-3">
                                        <form action="{{ route('dashboard.delivery-pricing.update', $row) }}" method="POST" class="inline-flex gap-1">
                                            @csrf
                                            @method('PUT')
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                name="min_km" 
                                                value="{{ old('min_km', $row->min_km) }}" 
                                                class="w-20 rounded border px-2 py-1 text-sm"
                                                onchange="this.form.submit()"
                                            >
                                            <span class="px-1">até</span>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                name="max_km" 
                                                value="{{ old('max_km', $row->max_km) }}" 
                                                class="w-20 rounded border px-2 py-1 text-sm"
                                                onchange="this.form.submit()"
                                            >
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <form action="{{ route('dashboard.delivery-pricing.update', $row) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="min_km" value="{{ $row->min_km }}">
                                            <input type="hidden" name="max_km" value="{{ $row->max_km }}">
                                            <input type="hidden" name="min_amount_free" value="{{ $row->min_amount_free ?? '' }}">
                                            <input type="hidden" name="is_active" value="{{ $row->is_active ? '1' : '0' }}">
                                            <input type="hidden" name="sort_order" value="{{ $row->sort_order }}">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                name="fee" 
                                                value="{{ old('fee', $row->fee) }}" 
                                                class="w-24 rounded border px-2 py-1 text-sm"
                                                onchange="this.form.submit()"
                                            >
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <form action="{{ route('dashboard.delivery-pricing.update', $row) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="min_km" value="{{ $row->min_km }}">
                                            <input type="hidden" name="max_km" value="{{ $row->max_km }}">
                                            <input type="hidden" name="fee" value="{{ $row->fee }}">
                                            <input type="hidden" name="is_active" value="{{ $row->is_active ? '1' : '0' }}">
                                            <input type="hidden" name="sort_order" value="{{ $row->sort_order }}">
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                name="min_amount_free" 
                                                value="{{ old('min_amount_free', $row->min_amount_free ?? '') }}" 
                                                placeholder="-" 
                                                class="w-24 rounded border px-2 py-1 text-sm"
                                                onchange="this.form.submit()"
                                            >
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <form action="{{ route('dashboard.delivery-pricing.update', $row) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="min_km" value="{{ $row->min_km }}">
                                            <input type="hidden" name="max_km" value="{{ $row->max_km }}">
                                            <input type="hidden" name="fee" value="{{ $row->fee }}">
                                            <input type="hidden" name="min_amount_free" value="{{ $row->min_amount_free ?? '' }}">
                                            <input type="hidden" name="is_active" value="{{ $row->is_active ? '1' : '0' }}">
                                            <input 
                                                type="number" 
                                                name="sort_order" 
                                                value="{{ old('sort_order', $row->sort_order) }}" 
                                                class="w-16 rounded border px-2 py-1 text-sm"
                                                onchange="this.form.submit()"
                                            >
                                        </form>
                                    </td>
                                    <td class="p-3">
                                        <form action="{{ route('dashboard.delivery-pricing.update', $row) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="min_km" value="{{ $row->min_km }}">
                                            <input type="hidden" name="max_km" value="{{ $row->max_km }}">
                                            <input type="hidden" name="fee" value="{{ $row->fee }}">
                                            <input type="hidden" name="min_amount_free" value="{{ $row->min_amount_free ?? '' }}">
                                            <input type="hidden" name="sort_order" value="{{ $row->sort_order }}">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input 
                                                    type="checkbox" 
                                                    name="is_active" 
                                                    value="1" 
                                                    {{ $row->is_active ? 'checked' : '' }}
                                                    onchange="this.form.submit()"
                                                    class="w-4 h-4"
                                                >
                                                <span class="text-sm {{ $row->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $row->is_active ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="p-3 text-right">
                                        <form 
                                            action="{{ route('dashboard.delivery-pricing.destroy', $row) }}" 
                                            method="POST" 
                                            class="inline"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta faixa?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-md text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 h-9 px-3"
                                            >
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-muted-foreground">
                    <p>Nenhuma faixa de distância configurada ainda.</p>
                    <p class="text-sm mt-2">Use o formulário acima para adicionar a primeira faixa.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para CEP
    const zipcodeInput = document.getElementById('simulate_zipcode');
    if (zipcodeInput) {
        zipcodeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 8);
            }
            e.target.value = value;
        });
    }

    // Formulário de simulação
    const simulateForm = document.getElementById('simulateForm');
    const resultDiv = document.getElementById('simulationResult');
    const errorDiv = document.getElementById('simulationError');

    if (simulateForm) {
        simulateForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const zipcode = document.getElementById('simulate_zipcode').value.replace(/\D/g, '');
            const subtotal = parseFloat(document.getElementById('simulate_subtotal').value) || 0;

            if (zipcode.length !== 8) {
                errorDiv.textContent = 'CEP inválido. Digite um CEP com 8 dígitos.';
                errorDiv.classList.remove('hidden');
                resultDiv.classList.add('hidden');
                return;
            }

            // Mostrar loading
            const submitBtn = simulateForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Calculando...';
            errorDiv.classList.add('hidden');
            resultDiv.classList.add('hidden');

            try {
                const formData = new FormData();
                formData.append('zipcode', zipcode);
                formData.append('subtotal', subtotal);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

                const response = await fetch('{{ route("dashboard.delivery-pricing.simulate") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Exibir resultados
                    document.getElementById('result_distance').textContent = data.distance_km || '-';
                    document.getElementById('result_base_fee').textContent = formatCurrency(data.base_delivery_fee || 0);
                    
                    if (data.discount_percent && data.discount_percent > 0) {
                        document.getElementById('result_discount_percent').textContent = data.discount_percent + '%';
                        document.getElementById('result_discount_amount').textContent = formatCurrency(data.discount_amount || 0);
                        document.getElementById('result_discount_row').classList.remove('hidden');
                    } else {
                        document.getElementById('result_discount_row').classList.add('hidden');
                    }
                    
                    document.getElementById('result_final_fee').textContent = formatCurrency(data.delivery_fee || 0);
                    
                    if (data.free || data.delivery_fee <= 0) {
                        document.getElementById('result_free').classList.remove('hidden');
                    } else {
                        document.getElementById('result_free').classList.add('hidden');
                    }

                    resultDiv.classList.remove('hidden');
                    errorDiv.classList.add('hidden');
                } else {
                    errorDiv.textContent = data.message || 'Erro ao calcular taxa de entrega.';
                    errorDiv.classList.remove('hidden');
                    resultDiv.classList.add('hidden');
                }
            } catch (error) {
                console.error('Erro:', error);
                errorDiv.textContent = 'Erro ao calcular taxa de entrega. Verifique sua conexão.';
                errorDiv.classList.remove('hidden');
                resultDiv.classList.add('hidden');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }
});
</script>
@endpush
@endsection
