@extends('dashboard.layouts.app')

@section('page_title', 'Cashback')
@section('page_subtitle', 'Gerenciamento do programa de cashback')

@section('content')
<div x-data="cashbackPage()" id="cashback-page" class="space-y-6">

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 border-green-200 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 border border-green-200">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="h-4 w-4 text-white"></i>
                </div>
                <span class="text-xs font-medium text-green-700 uppercase tracking-wide">Total Gerado</span>
            </div>
            <p class="text-xl font-bold text-green-800">R$ {{ number_format($totalCredits ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-4 border border-red-200">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                    <i data-lucide="minus-circle" class="h-4 w-4 text-white"></i>
                </div>
                <span class="text-xs font-medium text-red-700 uppercase tracking-wide">Total Utilizado</span>
            </div>
            <p class="text-xl font-bold text-red-800">R$ {{ number_format($totalDebits ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-gradient-to-br from-primary/5 to-primary/15 rounded-2xl p-4 border border-primary/20">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center">
                    <i data-lucide="wallet" class="h-4 w-4 text-white"></i>
                </div>
                <span class="text-xs font-medium text-primary uppercase tracking-wide">Saldo Disponível</span>
            </div>
            <p class="text-xl font-bold text-primary">R$ {{ number_format($totalAvailable ?? 0, 2, ',', '.') }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 border border-blue-200">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                    <i data-lucide="users" class="h-4 w-4 text-white"></i>
                </div>
                <span class="text-xs font-medium text-blue-700 uppercase tracking-wide">Clientes com Saldo</span>
            </div>
            <p class="text-xl font-bold text-blue-800">{{ $activeCustomers ?? 0 }}</p>
        </div>

    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Settings Card --}}
        <div class="bg-card rounded-xl border border-border overflow-hidden">
            <div class="p-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Configurações do Programa</h3>
                <p class="text-sm text-muted-foreground mt-0.5">Configure as regras do programa de cashback</p>
            </div>
            <div class="p-4">
                <form action="{{ route('dashboard.cashback.settings.save') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Toggle Ativo --}}
                    <label class="flex items-center justify-between bg-muted/20 p-4 rounded-xl border border-border/50 cursor-pointer">
                        <div>
                            <span class="font-medium text-foreground block">Programa Ativo</span>
                            <span class="text-xs text-muted-foreground">Ativar ou desativar o programa de cashback</span>
                        </div>
                        <div class="relative">
                            <input type="checkbox" name="cashback_enabled" value="1" {{ ($cashbackSettings['enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>

                    {{-- Percentage --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Percentual de Cashback (%)</label>
                        <input type="number" name="cashback_percentage" step="0.1" min="0" max="100"
                            value="{{ old('cashback_percentage', $cashbackSettings['percentage'] ?? 5.0) }}" required
                            class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        <p class="text-xs text-muted-foreground mt-1">Porcentagem do valor da compra devolvida como cashback.</p>
                    </div>

                    {{-- Min Purchase --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Compra Mínima (R$)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                            <input type="number" name="cashback_min_purchase" step="0.01" min="0"
                                value="{{ old('cashback_min_purchase', $cashbackSettings['min_purchase'] ?? 30.0) }}" required
                                class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Valor mínimo da compra para receber cashback.</p>
                    </div>

                    {{-- Max Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Cashback Máximo por Compra</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                            <input type="number" name="cashback_max_amount" step="0.01" min="0"
                                value="{{ old('cashback_max_amount', $cashbackSettings['max_amount'] ?? 50.0) }}"
                                class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                placeholder="0.00">
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Deixe 0 para ilimitado.</p>
                    </div>

                    {{-- Expiry Days --}}
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Validade (dias)</label>
                        <input type="number" name="cashback_expiry_days" min="1"
                            value="{{ old('cashback_expiry_days', $cashbackSettings['expiry_days'] ?? 90) }}" required
                            class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                            placeholder="90">
                        <p class="text-xs text-muted-foreground mt-1">Tempo até o cashback expirar se não for utilizado.</p>
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Salvar Configurações
                    </button>
                </form>
            </div>
        </div>

        {{-- Transactions Card --}}
        <div class="bg-card rounded-xl border border-border overflow-hidden">
            <div class="p-4 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">Últimas Transações</h3>
                    <p class="text-sm text-muted-foreground mt-0.5">Transações de cashback recentes</p>
                </div>
                <button type="button" @click="openModal('create')"
                    class="inline-flex items-center justify-center gap-2 h-9 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors shrink-0">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    <span>Nova Transação</span>
                </button>
            </div>
            <div class="p-4">
                @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentTransactions as $transaction)
                            @php
                                $transactionJson = [
                                    'id' => $transaction->id,
                                    'customer_id' => $transaction->customer_id,
                                    'customer_name' => $transaction->customer->name ?? 'Cliente não encontrado',
                                    'amount' => (float) $transaction->amount,
                                    'type' => $transaction->type,
                                    'description' => $transaction->description ?? '',
                                    'update_url' => route('dashboard.cashback.update', $transaction->id),
                                ];
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-xl border border-border bg-white hover:shadow-sm transition-all">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-9 h-9 rounded-lg {{ $transaction->type === 'credit' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center shrink-0">
                                        <i data-lucide="{{ $transaction->type === 'credit' ? 'plus' : 'minus' }}"
                                            class="h-4 w-4 {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-sm text-foreground truncate">{{ $transaction->customer->name ?? 'Cliente não encontrado' }}</p>
                                        <p class="text-xs text-muted-foreground truncate">{{ $transaction->description ?? 'Sem descrição' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="font-semibold text-sm {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->type === 'credit' ? '+' : '-' }}R$ {{ number_format((float)$transaction->amount, 2, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">{{ $transaction->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <button type="button" @click="openModal('edit', {{ json_encode($transactionJson) }})"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg hover:bg-muted transition-colors">
                                        <i data-lucide="edit" class="h-4 w-4 text-muted-foreground"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-muted-foreground">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="wallet" class="w-10 h-10 opacity-20"></i>
                            <p class="text-sm">Nenhuma transação de cashback registrada.</p>
                            <button @click="openModal('create')"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Criar primeira transação
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="fixed inset-0 z-[100] overflow-y-auto" x-show="isModalOpen" x-cloak
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="fixed inset-0 bg-black/50" @click="closeModal()"></div>

        <div class="flex min-h-full items-start sm:items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden my-auto"
                @click.stop
                x-show="isModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">

                <form :action="formAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditMode ? 'PUT' : 'POST'">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                        <div>
                            <h2 class="text-lg font-semibold text-foreground" x-text="isEditMode ? 'Editar Transação' : 'Nova Transação'"></h2>
                            <p class="text-sm text-muted-foreground mt-0.5">Ajuste manual de cashback do cliente.</p>
                        </div>
                        <button type="button" @click="closeModal()" class="p-2 rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-4">

                        {{-- Customer (only for create) --}}
                        <div x-show="!isEditMode">
                            <label class="block text-sm font-medium text-foreground mb-1">Cliente <span class="text-destructive">*</span></label>
                            <select name="customer_id" x-model="formData.customer_id" :required="!isEditMode"
                                class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                                <option value="">Selecione um cliente...</option>
                                @foreach(\App\Models\Customer::orderBy('name')->get() as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Customer Name (readonly for edit) --}}
                        <div x-show="isEditMode">
                            <label class="block text-sm font-medium text-foreground mb-1">Cliente</label>
                            <input type="text" :value="formData.customer_name" readonly
                                class="w-full h-10 px-3 rounded-xl border border-border bg-muted/30 text-sm text-muted-foreground">
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-2">Tipo <span class="text-destructive">*</span></label>
                            <div class="flex gap-2">
                                <button type="button" @click="formData.type = 'credit'"
                                    class="flex-1 px-4 py-2 text-sm font-medium rounded-xl border transition-colors"
                                    :class="formData.type === 'credit' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-white border-border text-foreground hover:bg-muted/30'">
                                    <i data-lucide="plus" class="inline h-4 w-4 mr-1"></i>
                                    Crédito
                                </button>
                                <button type="button" @click="formData.type = 'debit'"
                                    class="flex-1 px-4 py-2 text-sm font-medium rounded-xl border transition-colors"
                                    :class="formData.type === 'debit' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-white border-border text-foreground hover:bg-muted/30'">
                                    <i data-lucide="minus" class="inline h-4 w-4 mr-1"></i>
                                    Débito
                                </button>
                            </div>
                            <input type="hidden" name="type" :value="formData.type">
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Valor <span class="text-destructive">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">R$</span>
                                <input type="number" name="amount" step="0.01" min="0.01" x-model="formData.amount" required
                                    class="w-full h-10 pl-10 pr-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                    placeholder="0.00">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Descrição</label>
                            <input type="text" name="description" x-model="formData.description"
                                class="w-full h-10 px-3 rounded-xl border border-border bg-white focus:border-primary focus:ring-1 focus:ring-primary text-sm"
                                placeholder="Ex: Ajuste manual, bônus, etc.">
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-border flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <button type="button" @click="closeModal()"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-border bg-white text-foreground font-medium hover:bg-muted/30 transition-colors text-sm">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white font-semibold shadow-sm transition-colors text-sm">
                            <span x-text="isEditMode ? 'Salvar Alterações' : 'Criar Transação'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('cashbackPage', function() {
        return {
            isModalOpen: false,
            isEditMode: false,
            formAction: '',

            formData: {
                id: null,
                customer_id: '',
                customer_name: '',
                amount: '',
                type: 'credit',
                description: ''
            },

            openModal(mode, data = null) {
                this.isModalOpen = true;
                this.isEditMode = mode === 'edit';
                if (mode === 'create') {
                    this.formAction = "{{ route('dashboard.cashback.store') }}";
                    this.resetForm();
                } else {
                    this.formAction = data.update_url;
                    this.loadFormData(data);
                }
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            },

            closeModal() {
                this.isModalOpen = false;
            },

            resetForm() {
                this.formData = {
                    id: null,
                    customer_id: '',
                    customer_name: '',
                    amount: '',
                    type: 'credit',
                    description: ''
                };
            },

            loadFormData(data) {
                this.formData = {
                    id: data.id,
                    customer_id: data.customer_id || '',
                    customer_name: data.customer_name || '',
                    amount: data.amount || '',
                    type: data.type || 'credit',
                    description: data.description || ''
                };
            }
        };
    });
});
</script>
@endpush
@endsection
