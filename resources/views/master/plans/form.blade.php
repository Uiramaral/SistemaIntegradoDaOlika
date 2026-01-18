@extends('layouts.admin')

@section('title', isset($plan) ? 'Editar Plano' : 'Novo Plano')
@section('page_title', isset($plan) ? 'Editar Plano' : 'Novo Plano')
@section('page_subtitle', isset($plan) ? 'Atualize as informações do plano' : 'Configure um novo plano de assinatura')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <form action="{{ isset($plan) ? route('master.plans.update', $plan) : route('master.plans.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @if(isset($plan))
                @method('PUT')
            @endif

            {{-- Informações Básicas --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Informações Básicas</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-foreground mb-1">Nome do Plano *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $plan->name ?? '') }}" required
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Ex: Básico, WhatsApp, Premium">
                        @error('name')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-sm font-medium text-foreground mb-1">Slug (identificador)</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $plan->slug ?? '') }}"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="basico (preenchido automaticamente)">
                        <p class="text-xs text-muted-foreground mt-1">Se vazio, será gerado a partir do nome</p>
                    </div>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-foreground mb-1">Descrição</label>
                    <textarea name="description" id="description" rows="2"
                              class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                              placeholder="Descrição curta do plano">{{ old('description', $plan->description ?? '') }}</textarea>
                </div>
            </div>

            {{-- Preço --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Preço</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-foreground mb-1">Valor Mensal (R$) *</label>
                        <input type="number" name="price" id="price" value="{{ old('price', $plan->price ?? '') }}" required
                               step="0.01" min="0"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="99.90">
                    </div>
                    
                    <div>
                        <label for="trial_days" class="block text-sm font-medium text-foreground mb-1">Dias de Trial</label>
                        <input type="number" name="trial_days" id="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 0) }}"
                               min="0"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="0">
                    </div>
                </div>
            </div>

            {{-- Recursos --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Recursos Incluídos</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="max_products" class="block text-sm font-medium text-foreground mb-1">Limite de Produtos</label>
                        <input type="number" name="max_products" id="max_products" value="{{ old('max_products', $plan->max_products ?? '') }}"
                               min="0"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Deixe vazio para ilimitado">
                    </div>
                    
                    <div>
                        <label for="max_orders_per_month" class="block text-sm font-medium text-foreground mb-1">Pedidos por Mês</label>
                        <input type="number" name="max_orders_per_month" id="max_orders_per_month" value="{{ old('max_orders_per_month', $plan->max_orders_per_month ?? '') }}"
                               min="0"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Deixe vazio para ilimitado">
                    </div>
                    
                    <div>
                        <label for="max_users" class="block text-sm font-medium text-foreground mb-1">Usuários Permitidos</label>
                        <input type="number" name="max_users" id="max_users" value="{{ old('max_users', $plan->max_users ?? 1) }}"
                               min="1"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="1">
                    </div>
                    
                    <div>
                        <label for="max_whatsapp_instances" class="block text-sm font-medium text-foreground mb-1">Instâncias WhatsApp</label>
                        <input type="number" name="max_whatsapp_instances" id="max_whatsapp_instances" value="{{ old('max_whatsapp_instances', $plan->max_whatsapp_instances ?? 0) }}"
                               min="0"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="0">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-border cursor-pointer hover:bg-muted/30 transition">
                        <input type="checkbox" name="has_whatsapp" value="1" {{ old('has_whatsapp', $plan->has_whatsapp ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                        <div>
                            <span class="font-medium text-foreground">WhatsApp Integrado</span>
                            <p class="text-xs text-muted-foreground">Permite enviar mensagens automáticas</p>
                        </div>
                    </label>
                    
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-border cursor-pointer hover:bg-muted/30 transition">
                        <input type="checkbox" name="has_ai" value="1" {{ old('has_ai', $plan->has_ai ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                        <div>
                            <span class="font-medium text-foreground">Inteligência Artificial</span>
                            <p class="text-xs text-muted-foreground">Sugestões inteligentes e chatbot</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Features (lista de recursos) --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Lista de Recursos (exibição)</h3>
                <p class="text-sm text-muted-foreground">Um recurso por linha. Estes aparecem na página de planos.</p>
                
                <textarea name="features_text" id="features_text" rows="5"
                          class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
                          placeholder="Cardápio digital ilimitado
Pedidos online
Relatórios básicos
Suporte por email">{{ old('features_text', isset($plan) && $plan->features ? implode("\n", $plan->features) : '') }}</textarea>
            </div>

            {{-- Configurações --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Configurações</h3>
                
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="active" value="1" {{ old('active', $plan->active ?? true) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                        <span class="text-foreground">Plano Ativo</span>
                    </label>
                    
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $plan->is_featured ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                        <span class="text-foreground">Destacar como "Mais Popular"</span>
                    </label>
                </div>
            </div>

            {{-- Ações --}}
            <div class="flex items-center gap-3 pt-4 border-t border-border">
                <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition font-medium">
                    {{ isset($plan) ? 'Atualizar Plano' : 'Criar Plano' }}
                </button>
                <a href="{{ route('master.plans.index') }}" class="px-6 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
