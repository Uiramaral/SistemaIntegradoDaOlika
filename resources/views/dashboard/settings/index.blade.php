@extends('dashboard.layouts.app')

@section('title', 'Configurações - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Configurações</h1>
        <p class="text-muted-foreground">Ajuste integrações e chaves de API do sistema</p>
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

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">APIs & Integrações</h3>
                <p class="text-sm text-muted-foreground">OpenAI e Google Maps</p>
            </div>
            <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="openai_api_key">OpenAI API Key</label>
                    <input name="openai_api_key" id="openai_api_key" type="password" value="{{ $apiSettings['openai_api_key'] ?? '' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="sk-...">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="openai_model">OpenAI Model</label>
                    <input name="openai_model" id="openai_model" value="{{ $apiSettings['openai_model'] ?? 'gpt-4o-mini' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="google_maps_api_key">Google Maps API Key</label>
                    <input name="google_maps_api_key" id="google_maps_api_key" type="password" value="{{ $apiSettings['google_maps_api_key'] ?? '' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="AIza...">
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Salvar</button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Pedidos</h3>
                <p class="text-sm text-muted-foreground">Numeração e referência externa</p>
            </div>
            <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="order_number_prefix">Prefixo do Número do Pedido</label>
                    <input name="order_number_prefix" id="order_number_prefix" value="{{ $apiSettings['order_number_prefix'] ?? 'OLK-' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Ex.: OLK-">
                    <p class="text-xs text-muted-foreground">Será usado na exibição e como external_reference no Mercado Pago.</p>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="next_order_number">Próximo Número</label>
                    <input name="next_order_number" id="next_order_number" type="number" min="1" value="{{ (int)($apiSettings['next_order_number'] ?? 1) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <p class="text-xs text-muted-foreground">A contagem seguirá a partir deste valor.</p>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="free_shipping_min_total">Frete Grátis a partir de (R$)</label>
                    <input name="free_shipping_min_total" id="free_shipping_min_total" type="number" step="0.01" min="0" value="{{ isset($apiSettings['free_shipping_min_total']) ? (float)$apiSettings['free_shipping_min_total'] : 200 }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Ex.: 200.00">
                    <p class="text-xs text-muted-foreground">Ao atingir este mínimo no subtotal, a entrega fica gratuita.</p>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Salvar</button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Taxas de Entrega</h3>
                <p class="text-sm text-muted-foreground">Configure faixas de distância e taxas</p>
            </div>
            <div class="p-6 pt-0">
                <a href="{{ route('dashboard.delivery-pricing.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Gerenciar Taxas de Entrega</a>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Cashback</h3>
                <p class="text-sm text-muted-foreground">Programa de fidelidade e recompensas</p>
            </div>
            <div class="p-6 pt-0">
                <a href="{{ route('dashboard.cashback.index') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Abrir Configurações de Cashback</a>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Mercado Pago</h3>
                <p class="text-sm text-muted-foreground">Integração de pagamentos</p>
            </div>
            <div class="p-6 pt-0">
                <a href="{{ route('dashboard.settings.mp') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Abrir Configurações</a>
            </div>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Agendamento de Entrega</h3>
                <p class="text-sm text-muted-foreground">Capacidade e prazos</p>
            </div>
            <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                @csrf
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium" for="delivery_slot_capacity">Capacidade por slot (30min)</label>
                        <input name="delivery_slot_capacity" id="delivery_slot_capacity" type="number" min="1" max="20" value="{{ $apiSettings['delivery_slot_capacity'] ?? 2 }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div>
                        <label class="text-sm font-medium" for="advance_order_days">Dias de antecedência mínimos</label>
                        <input name="advance_order_days" id="advance_order_days" type="number" min="0" max="30" value="{{ $apiSettings['advance_order_days'] ?? 2 }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div>
                        <label class="text-sm font-medium" for="default_cutoff_time">Cutoff diário (hh:mm)</label>
                        <input name="default_cutoff_time" id="default_cutoff_time" type="time" value="{{ $apiSettings['default_cutoff_time'] ?? '' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard.settings.delivery.schedules.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border h-10 px-4">Gerenciar dias e horários</a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Salvar</button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">BotConversa</h3>
                <p class="text-sm text-muted-foreground">Webhook para envio de notificações</p>
            </div>
            <form action="{{ route('dashboard.settings.apis.save') }}" method="POST" class="p-6 pt-0 space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="botconversa_webhook_url">URL do Webhook</label>
                    @php
                        $webhookUrl = $apiSettings['botconversa_webhook_url'] ?? '';
                        // Se for um email, limpar e usar .env
                        if (!empty($webhookUrl) && strpos($webhookUrl, '@') !== false && !filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
                            $webhookUrl = config('services.botconversa.webhook_url') ?: '';
                        }
                    @endphp
                    <input name="botconversa_webhook_url" id="botconversa_webhook_url" type="text" value="{{ $webhookUrl }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="https://new-backend.botconversa.com.br/api/v1/webhooks-automation/catch/...">
                    <p class="text-xs text-muted-foreground">URL para envio de notificações de pedidos pagos.</p>
                    @if(config('services.botconversa.webhook_url'))
                        <p class="text-xs text-blue-600">Valor atual do .env: {{ config('services.botconversa.webhook_url') }}</p>
                    @endif
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="botconversa_paid_webhook_url">URL do Webhook (Pedidos Pagos)</label>
                    @php
                        $paidWebhookUrl = $apiSettings['botconversa_paid_webhook_url'] ?? '';
                        // Se for um email (contém @ mas não é URL), limpar e usar .env
                        if (!empty($paidWebhookUrl) && strpos($paidWebhookUrl, '@') !== false && !filter_var($paidWebhookUrl, FILTER_VALIDATE_URL)) {
                            $paidWebhookUrl = config('services.botconversa.paid_webhook') ?: '';
                        }
                    @endphp
                    <input name="botconversa_paid_webhook_url" id="botconversa_paid_webhook_url" type="text" value="{{ $paidWebhookUrl }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="https://new-backend.botconversa.com.br/api/v1/webhooks-automation/catch/...">
                    <p class="text-xs text-muted-foreground">Opcional: URL específica para pedidos pagos. Se vazio, usa a URL padrão.</p>
                    @if(config('services.botconversa.paid_webhook'))
                        <p class="text-xs text-blue-600">Valor atual do .env: {{ config('services.botconversa.paid_webhook') }}</p>
                    @endif
                    @error('botconversa_paid_webhook_url')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium" for="botconversa_token">Token (opcional)</label>
                    <input name="botconversa_token" id="botconversa_token" type="password" value="{{ $apiSettings['botconversa_token'] ?? '' }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Token de autenticação (se necessário)">
                    <p class="text-xs text-muted-foreground">Token para autenticação no webhook (Bearer token).</p>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
