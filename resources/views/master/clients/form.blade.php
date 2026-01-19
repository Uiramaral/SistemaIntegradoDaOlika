@extends('layouts.admin')

@section('title', isset($client) ? 'Editar Cliente' : 'Novo Cliente')
@section('page_title', isset($client) ? 'Editar Cliente' : 'Novo Cliente')
@section('page_subtitle', isset($client) ? 'Atualize os dados do estabelecimento' : 'Cadastre um novo estabelecimento')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <form action="{{ isset($client) ? route('master.clients.update', $client) : route('master.clients.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @if(isset($client))
                @method('PUT')
            @endif

            {{-- Informações Básicas --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Informações do Estabelecimento</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-foreground mb-1">Nome do Estabelecimento *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $client->name ?? '') }}" required
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Ex: Restaurante da Maria">
                        @error('name')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    @if(!isset($client))
                    <div>
                        <label for="slug" class="block text-sm font-medium text-foreground mb-1">Subdomínio (URL) *</label>
                        <div class="flex items-center">
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                                   class="flex-1 px-3 py-2 rounded-l-md border border-r-0 border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
                                   placeholder="restaurante-maria"
                                   pattern="[a-z0-9-]+"
                                   oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '')">
                            <span class="px-3 py-2 bg-muted border border-border rounded-r-md text-sm text-muted-foreground">.menuolika.com.br</span>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Apenas letras minúsculas, números e hífens. NÃO pode ser alterado depois!</p>
                        @error('slug')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Subdomínio (URL)</label>
                        <div class="px-3 py-2 rounded-md bg-muted border border-border text-muted-foreground font-mono text-sm">
                            {{ $client->slug }}.menuolika.com.br
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">O subdomínio não pode ser alterado após criação</p>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-foreground mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $client->email ?? '') }}"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="contato@exemplo.com.br">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-foreground mb-1">Telefone</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone ?? '') }}"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="(71) 99999-9999">
                    </div>
                </div>

                <div>
                    <label for="whatsapp_phone" class="block text-sm font-medium text-foreground mb-1">WhatsApp para Notificações</label>
                    <input type="text" name="whatsapp_phone" id="whatsapp_phone" value="{{ old('whatsapp_phone', $client->whatsapp_phone ?? '') }}"
                           class="w-full max-w-xs px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="5571999999999">
                    <p class="text-xs text-muted-foreground mt-1">Formato internacional sem espaços</p>
                </div>
            </div>

            {{-- Plano e Assinatura --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Plano e Assinatura</h3>
                
                <div>
                    <label for="plan_id" class="block text-sm font-medium text-foreground mb-1">Plano</label>
                    <select name="plan_id" id="plan_id"
                            class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Selecione um plano...</option>
                        @foreach($plans ?? [] as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id', $client->subscription?->plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - {{ $plan->formatted_price }}/mês
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(!isset($client))
                <label class="flex items-center gap-3 p-3 rounded-lg border border-border cursor-pointer hover:bg-muted/30 transition">
                    <input type="checkbox" name="start_trial" value="1" checked
                           class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                    <div>
                        <span class="font-medium text-foreground">Iniciar em período de trial</span>
                        <p class="text-xs text-muted-foreground">Cliente terá {{ config('olika.trial_days', 7) }} dias gratuitos</p>
                    </div>
                </label>
                @endif
            </div>

            {{-- Configurações --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Configurações</h3>
                
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="active" value="1" {{ old('active', $client->active ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                    <span class="text-foreground">Cliente Ativo</span>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-primary/30 bg-primary/5 cursor-pointer hover:bg-primary/10 transition">
                    <input type="checkbox" name="is_master" value="1" {{ old('is_master', $client->is_master ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                    <div>
                        <span class="font-medium text-foreground">🏢 Estabelecimento Master</span>
                        <p class="text-xs text-muted-foreground">Marca como estabelecimento proprietário do SaaS (sem comissão)</p>
                    </div>
                </label>
            </div>

            {{-- Comissão Mercado Pago (OBRIGATÓRIA) --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">💳 Split de Pagamentos - Mercado Pago</h3>
                
                <!-- Alerta Informativo -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg space-y-2">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold text-blue-900 text-sm">Taxa Obrigatória de Serviço SaaS</p>
                            <p class="text-xs text-blue-700 mt-1">
                                <strong>Todas as vendas</strong> via Mercado Pago têm uma taxa fixa cobrada automaticamente. 
                                O valor vai <strong>direto</strong> para o estabelecimento, mas a taxa é "splitada" e cai na conta Olika.
                            </p>
                        </div>
                    </div>
                    
                    <div class="pl-7 text-xs text-blue-700 space-y-1">
                        <p>• <strong>Exemplo:</strong> Venda de R$ 100,00 → Cliente paga R$ 100,00</p>
                        <p>• Estabelecimento recebe: R$ 99,51 (descontadas taxas MP + taxa SaaS)</p>
                        <p>• Olika recebe: R$ 0,49 (taxa de serviço automaticamente)</p>
                        <p class="font-semibold mt-2">✅ O cliente final NÃO paga a mais. A taxa é deduzida do valor do estabelecimento.</p>
                    </div>
                </div>
                
                <!-- Configuração da Taxa -->
                <div class="p-4 bg-card border border-border rounded-lg space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-foreground">Taxa de Serviço por Venda (Application Fee)</p>
                            <p class="text-xs text-muted-foreground">Cobrado automaticamente em cada transação Mercado Pago</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full">ATIVO</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="mercadopago_commission_amount" class="block text-sm font-medium text-foreground mb-1">Valor da Taxa (R$) *</label>
                            <input type="number" step="0.01" min="0.01" name="mercadopago_commission_amount" id="mercadopago_commission_amount" 
                                   value="{{ old('mercadopago_commission_amount', $client->mercadopago_commission_amount ?? 0.49) }}" required
                                   class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                            <p class="text-xs text-muted-foreground mt-1">Valor fixo por transação (padrão: R$ 0,49)</p>
                        </div>
                        
                        <div class="flex items-end">
                            <div class="p-3 bg-muted rounded-lg w-full">
                                <p class="text-xs text-muted-foreground mb-1">Receita mensal estimada (100 vendas):</p>
                                <p class="text-lg font-bold text-foreground" id="monthly-estimate">R$ 49,00</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden field - comissão sempre ativa -->
                    <input type="hidden" name="mercadopago_commission_enabled" value="1">
                </div>
                
                <!-- Instruções Técnicas -->
                <details class="border border-border rounded-lg">
                    <summary class="px-4 py-3 bg-muted cursor-pointer hover:bg-muted/80 transition font-medium text-sm">
                        📄 Instruções para o Estabelecimento (OAuth + Split)
                    </summary>
                    <div class="p-4 space-y-3 text-xs text-foreground">
                        <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                            <p class="font-semibold text-yellow-900 mb-1">⚠️ O estabelecimento DEVE seguir estes passos:</p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="font-semibold">1️⃣ Criar Conta Mercado Pago (Vendedor)</p>
                            <p class="pl-4">• Acessar: <a href="https://www.mercadopago.com.br/hub/registration/landing" target="_blank" class="text-primary underline">mercadopago.com.br</a></p>
                            <p class="pl-4">• Cadastrar como <strong>Pessoa Jurídica</strong> (CNPJ do estabelecimento)</p>
                            <p class="pl-4">• Completar 100% do cadastro e validação de identidade</p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="font-semibold">2️⃣ Gerar Credenciais da API</p>
                            <p class="pl-4">• Acessar: <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" class="text-primary underline">Painel de Desenvolvedores</a></p>
                            <p class="pl-4">• Criar uma nova aplicação (nome: "Integração {{ config('app.name') }}")</p>
                            <p class="pl-4">• Copiar: <strong>Public Key</strong> e <strong>Access Token</strong></p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="font-semibold">3️⃣ Autorizar o Split (OAuth)</p>
                            <p class="pl-4">• No painel do cliente, clicar em <strong>"Conectar Mercado Pago"</strong></p>
                            <p class="pl-4">• Autorizar a aplicação {{ config('app.name') }} a processar pagamentos</p>
                            <p class="pl-4">• Isso permite que a taxa SaaS seja cobrada automaticamente</p>
                        </div>
                        
                        <div class="space-y-2">
                            <p class="font-semibold">4️⃣ Configurar Webhook</p>
                            <p class="pl-4">• URL do Webhook: <code class="bg-muted px-1 py-0.5 rounded">{{ url('/webhooks/mercadopago') }}</code></p>
                            <p class="pl-4">• Eventos: <strong>payment, merchant_order</strong></p>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 rounded p-3 mt-3">
                            <p class="font-semibold text-green-900">✅ Como Funciona o Split:</p>
                            <p class="mt-1">• Cliente final paga R$ 100,00</p>
                            <p>• Mercado Pago cobra ~4% deles (taxa de processamento)</p>
                            <p>• {{ config('app.name') }} cobra R$ {{ number_format($client->mercadopago_commission_amount ?? 0.49, 2, ',', '.') }} (application_fee)</p>
                            <p>• Estabelecimento recebe o restante na conta deles</p>
                            <p class="font-semibold mt-2">💰 Total para estabelecimento: ~R$ 95,51</p>
                        </div>
                    </div>
                </details>
            </div>
            
            {{-- Acesso Vitalício/Gratuito --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">♻️ Acesso Vitalício Gratuito</h3>
                <p class="text-sm text-muted-foreground">Conceda acesso vitalício sem necessidade de renovação mensal (ideal para fundadores, parceiros e testers)</p>
                
                <label class="flex items-center gap-3 p-3 rounded-lg border border-green-500/30 bg-green-500/5 cursor-pointer hover:bg-green-500/10 transition">
                    <input type="checkbox" name="is_lifetime_free" id="is_lifetime_free" value="1" 
                           {{ old('is_lifetime_free', $client->is_lifetime_free ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-border text-green-600 focus:ring-green-500"
                           onchange="document.getElementById('lifetime-config').classList.toggle('hidden', !this.checked)">
                    <div>
                        <span class="font-medium text-foreground">✨ Ativar Acesso Vitalício Gratuito</span>
                        <p class="text-xs text-muted-foreground">Cliente terá acesso permanente sem cobranças mensais</p>
                    </div>
                </label>

                <div id="lifetime-config" class="space-y-4 pl-8 {{ old('is_lifetime_free', $client->is_lifetime_free ?? false) ? '' : 'hidden' }}">
                    <div>
                        <label for="lifetime_plan" class="block text-sm font-medium text-foreground mb-1">Plano Vitalício</label>
                        <select name="lifetime_plan" id="lifetime_plan"
                                class="w-full max-w-xs px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Selecione o plano...</option>
                            <option value="basic" {{ old('lifetime_plan', $client->lifetime_plan ?? '') === 'basic' ? 'selected' : '' }}>Básico</option>
                            <option value="ia" {{ old('lifetime_plan', $client->lifetime_plan ?? '') === 'ia' ? 'selected' : '' }}>IA (Completo)</option>
                            <option value="custom" {{ old('lifetime_plan', $client->lifetime_plan ?? '') === 'custom' ? 'selected' : '' }}>Customizado</option>
                        </select>
                        <p class="text-xs text-muted-foreground mt-1">Plano que o cliente terá permanentemente</p>
                    </div>
                    
                    <div>
                        <label for="lifetime_reason" class="block text-sm font-medium text-foreground mb-1">Motivo/Justificativa</label>
                        <input type="text" name="lifetime_reason" id="lifetime_reason" 
                               value="{{ old('lifetime_reason', $client->lifetime_reason ?? '') }}"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Ex: Fundador, Parceiro Estratégico, Tester Beta, Cortesia">
                        <p class="text-xs text-muted-foreground mt-1">Registre o motivo da concessão do acesso vitalício</p>
                    </div>
                    
                    @if(isset($client) && $client->lifetime_granted_at)
                    <div class="p-3 bg-muted rounded-lg border border-border">
                        <p class="text-xs font-medium text-foreground">📅 Concedido em: {{ $client->lifetime_granted_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if(!isset($client))
            {{-- Usuário Admin --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground border-b border-border pb-2">Usuário Administrador</h3>
                <p class="text-sm text-muted-foreground">Será criado automaticamente um usuário admin para o estabelecimento</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="admin_name" class="block text-sm font-medium text-foreground mb-1">Nome do Admin</label>
                        <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}"
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="Nome do administrador">
                    </div>
                    
                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-foreground mb-1">Email do Admin *</label>
                        <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required
                               class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                               placeholder="admin@exemplo.com.br">
                    </div>
                </div>

                <div>
                    <label for="admin_password" class="block text-sm font-medium text-foreground mb-1">Senha *</label>
                    <input type="password" name="admin_password" id="admin_password" required minlength="6"
                           class="w-full max-w-xs px-3 py-2 rounded-md border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                           placeholder="Mínimo 6 caracteres">
                </div>
            </div>
            @endif

            {{-- Ações --}}
            <div class="flex items-center gap-3 pt-4 border-t border-border">
                <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition font-medium">
                    {{ isset($client) ? 'Atualizar Cliente' : 'Criar Cliente' }}
                </button>
                <a href="{{ route('master.clients.index') }}" class="px-6 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@if(!isset($client))
@push('scripts')
<script>
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (slugInput && !slugInput.dataset.userModified) {
        slugInput.value = this.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Remove accents
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .substring(0, 30);
    }
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.userModified = 'true';
});
</script>
@endpush
@endif

{{-- Script para calcular estimativa de receita mensal --}}
@push('scripts')
<script>
// Calcular estimativa de receita mensal
function updateMonthlyEstimate() {
    const commissionInput = document.getElementById('mercadopago_commission_amount');
    const estimateDisplay = document.getElementById('monthly-estimate');
    
    if (commissionInput && estimateDisplay) {
        const commission = parseFloat(commissionInput.value) || 0;
        const monthlyRevenue = commission * 100; // 100 vendas por mês
        
        estimateDisplay.textContent = 'R$ ' + monthlyRevenue.toFixed(2).replace('.', ',');
    }
}

// Atualizar ao carregar a página
document.addEventListener('DOMContentLoaded', updateMonthlyEstimate);

// Atualizar ao digitar
const commissionInput = document.getElementById('mercadopago_commission_amount');
if (commissionInput) {
    commissionInput.addEventListener('input', updateMonthlyEstimate);
}
</script>
@endpush
@endsection
