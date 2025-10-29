@extends('layouts.admin')

@section('title', 'Configurações')
@section('page_title', 'Configurações')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card title="Dados da Loja">
        <form action="/settings/store" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Loja</label>
                    <input type="text" name="store_name" class="input" value="{{ $settings['store_name'] ?? 'Olika' }}" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
                    <input type="text" name="store_cnpj" class="input" value="{{ $settings['store_cnpj'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                    <input type="email" name="store_email" class="input" value="{{ $settings['store_email'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                    <input type="text" name="store_phone" class="input" value="{{ $settings['store_phone'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                    <input type="text" name="store_address" class="input" value="{{ $settings['store_address'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                    <input type="text" name="store_city" class="input" value="{{ $settings['store_city'] ?? '' }}">
                </div>
            </div>
            <div class="mt-4">
                <x-button type="submit" variant="primary">
                    <i class="fas fa-save"></i> Salvar Dados
                </x-button>
            </div>
        </form>
    </x-card>
    
    <x-card title="Configurações de Venda">
        <form action="/settings/sales" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Taxa de Serviço (%)</label>
                    <input type="number" name="service_fee" step="0.01" class="input" value="{{ $settings['service_fee'] ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pedido Mínimo (R$)</label>
                    <input type="number" name="min_order" step="0.01" class="input" value="{{ $settings['min_order'] ?? 0 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tempo de Preparo (min)</label>
                    <input type="number" name="prep_time" class="input" value="{{ $settings['prep_time'] ?? 30 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Taxa de Entrega (R$)</label>
                    <input type="number" name="delivery_fee" step="0.01" class="input" value="{{ $settings['delivery_fee'] ?? 0 }}">
                </div>
            </div>
            <div class="mt-4">
                <x-button type="submit" variant="primary">
                    <i class="fas fa-save"></i> Salvar Configurações
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <x-card title="WhatsApp">
        <form action="/settings/whatsapp" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Token</label>
                    <input type="text" name="whatsapp_token" class="input" value="{{ $settings['whatsapp_token'] ?? '' }}" placeholder="Digite o token do WhatsApp">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                    <input type="text" name="whatsapp_number" class="input" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="Ex: 5511999999999">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mensagem Padrão</label>
                    <textarea name="whatsapp_message" class="input" rows="3" placeholder="Mensagem enviada automaticamente">{{ $settings['whatsapp_message'] ?? '' }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <x-button type="submit" variant="primary">
                    <i class="fas fa-save"></i> Salvar WhatsApp
                </x-button>
            </div>
        </form>
    </x-card>
    
    <x-card title="Mercado Pago">
        <form action="/settings/mercadopago" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Access Token</label>
                    <input type="text" name="mp_token" class="input" value="{{ $settings['mp_token'] ?? '' }}" placeholder="Digite o access token">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Public Key</label>
                    <input type="text" name="mp_public_key" class="input" value="{{ $settings['mp_public_key'] ?? '' }}" placeholder="Digite a public key">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Webhook URL</label>
                    <input type="text" name="mp_webhook_url" class="input" value="{{ $settings['mp_webhook_url'] ?? url('/webhooks/mercadopago') }}" readonly>
                </div>
            </div>
            <div class="mt-4">
                <x-button type="submit" variant="primary">
                    <i class="fas fa-save"></i> Salvar Mercado Pago
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<x-card title="Configurações Avançadas" class="mt-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Sistema</h3>
            <div class="space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="maintenance_mode" class="mr-2" {{ $settings['maintenance_mode'] ?? false ? 'checked' : '' }}>
                    Modo Manutenção
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="auto_confirm_orders" class="mr-2" {{ $settings['auto_confirm_orders'] ?? false ? 'checked' : '' }}>
                    Confirmar Pedidos Automaticamente
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="send_notifications" class="mr-2" {{ $settings['send_notifications'] ?? true ? 'checked' : '' }}>
                    Enviar Notificações
                </label>
            </div>
        </div>
        
        <div>
            <h3 class="text-lg font-semibold mb-4">Cashback</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Percentual (%)</label>
                    <input type="number" name="cashback_percentage" step="0.01" class="input" value="{{ $settings['cashback_percentage'] ?? 5 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Mínimo (R$)</label>
                    <input type="number" name="cashback_min_value" step="0.01" class="input" value="{{ $settings['cashback_min_value'] ?? 10 }}">
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-lg font-semibold mb-4">Fidelidade</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pontos por Real</label>
                    <input type="number" name="points_per_real" class="input" value="{{ $settings['points_per_real'] ?? 1 }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pontos para Resgate</label>
                    <input type="number" name="points_for_redeem" class="input" value="{{ $settings['points_for_redeem'] ?? 100 }}">
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-6">
        <x-button variant="primary">
            <i class="fas fa-save"></i> Salvar Todas as Configurações
        </x-button>
    </div>
</x-card>
@endsection