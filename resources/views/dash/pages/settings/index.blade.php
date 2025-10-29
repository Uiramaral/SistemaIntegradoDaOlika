@extends('layouts.admin')

@section('title', 'Configurações')
@section('page_title', 'Configurações')

@section('content')
@if(session('success'))
    <x-alert type="success">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </x-alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-card>
        <h2 class="text-xl font-bold mb-4">Dados da Loja</h2>
        <form action="/settings/store" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form-group label="Nome da Loja" required>
                    <x-input name="store_name" value="{{ $storeSettings['store_name'] ?? 'Olika' }}" required />
                </x-form-group>
                
                <x-form-group label="CNPJ">
                    <x-input name="store_cnpj" value="{{ $storeSettings['store_cnpj'] ?? '' }}" />
                </x-form-group>
                
                <x-form-group label="E-mail">
                    <x-input type="email" name="store_email" value="{{ $storeSettings['store_email'] ?? '' }}" />
                </x-form-group>
                
                <x-form-group label="Telefone">
                    <x-input name="store_phone" value="{{ $storeSettings['store_phone'] ?? '' }}" />
                </x-form-group>
            </div>
            <div class="mt-4">
                <x-button variant="primary">
                    <i class="fas fa-save mr-2"></i> Salvar Dados
                </x-button>
            </div>
        </form>
    </x-card>
    
    <x-card>
        <h2 class="text-xl font-bold mb-4">Configurações de Venda</h2>
        <form action="/settings/sales" method="POST">
            @csrf
            <div class="space-y-4">
                <x-form-group label="Taxa de Serviço (%)">
                    <x-input type="number" name="service_fee" step="0.01" value="{{ $storeSettings['service_fee'] ?? 0 }}" />
                </x-form-group>
                
                <x-form-group label="Pedido Mínimo (R$)">
                    <x-input type="number" name="min_order" step="0.01" value="{{ $storeSettings['min_order'] ?? 0 }}" />
                </x-form-group>
            </div>
            <div class="mt-4">
                <x-button variant="primary">
                    <i class="fas fa-save mr-2"></i> Salvar Configurações
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <x-card>
        <h2 class="text-xl font-bold mb-4">WhatsApp</h2>
        <form action="{{ route('dashboard.settings.whatsapp') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-form-group label="API URL">
                    <x-input name="api_url" value="{{ $whatsappSettings->api_url ?? '' }}" placeholder="https://api.whatsapp.com" />
                </x-form-group>
                
                <x-form-group label="Instance Name">
                    <x-input name="instance_name" value="{{ $whatsappSettings->instance_name ?? '' }}" placeholder="Nome da instância" />
                </x-form-group>
                
                <x-form-group label="API Key">
                    <x-input name="api_key" value="{{ $whatsappSettings->api_key ?? '' }}" placeholder="Chave da API" />
                </x-form-group>
            </div>
            <div class="mt-4">
                <x-button variant="primary">
                    <i class="fas fa-save mr-2"></i> Salvar WhatsApp
                </x-button>
            </div>
        </form>
    </x-card>
    
    <x-card>
        <h2 class="text-xl font-bold mb-4">Mercado Pago</h2>
        <form action="{{ route('dashboard.settings.mp') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <x-form-group label="Access Token">
                    <x-input name="mercadopago_access_token" value="{{ $paymentSettings['mercadopago_access_token'] ?? '' }}" placeholder="Digite o access token" />
                </x-form-group>
                
                <x-form-group label="Public Key">
                    <x-input name="mercadopago_public_key" value="{{ $paymentSettings['mercadopago_public_key'] ?? '' }}" placeholder="Digite a public key" />
                </x-form-group>
                
                <x-form-group label="Environment">
                    <select name="mercadopago_environment" class="input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-orange-500 focus:border-orange-500">
                        <option value="sandbox" {{ ($paymentSettings['mercadopago_environment'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="production" {{ ($paymentSettings['mercadopago_environment'] ?? '') == 'production' ? 'selected' : '' }}>Produção</option>
                    </select>
                </x-form-group>
            </div>
            <div class="mt-4">
                <x-button variant="primary">
                    <i class="fas fa-save mr-2"></i> Salvar Mercado Pago
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection