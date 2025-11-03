@extends('dashboard.layouts.app')

@section('title', 'Editar Cupom - OLIKA Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Editar Cupom</h1>
            <p class="text-muted-foreground">Atualize as informações do cupom</p>
        </div>
        <a href="{{ route('dashboard.coupons.index') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 text-red-700 p-4">
            <div class="font-semibold mb-1">Erros encontrados:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.coupons.update', $coupon) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-lg border bg-card p-6 space-y-6">
            <!-- Informações Básicas -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Informações Básicas</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Código do Cupom *</label>
                        <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Ex: DESCONTO10">
                        <p class="text-xs text-muted-foreground mt-1">Será convertido para maiúsculas automaticamente</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Nome *</label>
                        <input type="text" name="name" value="{{ old('name', $coupon->name) }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Ex: Desconto de 10%">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-2">Descrição</label>
                    <textarea name="description" rows="2" class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">{{ old('description', $coupon->description) }}</textarea>
                </div>
            </div>

            <!-- Tipo de Desconto -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Desconto</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo *</label>
                        <select name="type" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentual (%)</option>
                            <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Valor *</label>
                        <input type="number" name="value" step="0.01" min="0" value="{{ old('value', $coupon->value) }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-2">Valor Mínimo do Pedido (opcional)</label>
                    <input type="number" name="minimum_amount" step="0.01" min="0" value="{{ old('minimum_amount', $coupon->minimum_amount) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>
            </div>

            <!-- Visibilidade e Elegibilidade -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Visibilidade e Elegibilidade</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Visibilidade *</label>
                        <select name="visibility" id="visibility" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="public" {{ old('visibility', $coupon->visibility) == 'public' ? 'selected' : '' }}>Público (visível para todos elegíveis)</option>
                            <option value="private" {{ old('visibility', $coupon->visibility) == 'private' ? 'selected' : '' }}>Privado (precisa digitar código)</option>
                            <option value="targeted" {{ old('visibility', $coupon->visibility) == 'targeted' ? 'selected' : '' }}>Direcionado (cliente específico)</option>
                        </select>
                    </div>
                    <div id="targetCustomerField" style="display: {{ old('visibility', $coupon->visibility) == 'targeted' ? 'block' : 'none' }}">
                        <label class="block text-sm font-medium mb-2">Cliente Alvo</label>
                        <select name="target_customer_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <option value="">Selecione um cliente</option>
                            @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ old('target_customer_id', $coupon->target_customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="first_order_only" value="1" {{ old('first_order_only', $coupon->first_order_only) ? 'checked' : '' }} class="h-4 w-4">
                        <span class="text-sm font-medium">Apenas para primeiro pedido</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="free_shipping_only" value="1" {{ old('free_shipping_only', $coupon->free_shipping_only) ? 'checked' : '' }} class="h-4 w-4">
                        <span class="text-sm font-medium">Apenas para frete grátis (quando há frete no pedido e não ganhou frete grátis por valor mínimo)</span>
                    </label>
                </div>
            </div>

            <!-- Limites de Uso -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Limites de Uso</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Limite Total (opcional)</label>
                        <input type="number" name="usage_limit" min="1" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Deixe vazio para ilimitado">
                        <p class="text-xs text-muted-foreground mt-1">Usado: {{ $coupon->used_count ?? 0 }} vezes</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Limite por Cliente (opcional)</label>
                        <input type="number" name="usage_limit_per_customer" min="1" value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Deixe vazio para ilimitado">
                    </div>
                </div>
            </div>

            <!-- Validade -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Validade</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Data de Início (opcional)</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Data de Expiração (opcional)</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }} class="h-4 w-4">
                    <span class="text-sm font-medium">Cupom ativo</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('dashboard.coupons.index') }}" class="px-4 py-2 rounded-md border hover:bg-accent">Cancelar</a>
            <button type="submit" class="px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90">Salvar Alterações</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('visibility').addEventListener('change', function() {
    const targetField = document.getElementById('targetCustomerField');
    if (this.value === 'targeted') {
        targetField.style.display = 'block';
    } else {
        targetField.style.display = 'none';
    }
});
</script>
@endpush
@endsection
