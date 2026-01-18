@extends('dashboard.layouts.app')

@section('title', 'WhatsApp - Painel OLIKA')

@php
// Mapeamento de slugs para nomes amigáveis
$templateLabels = [
    'cancelado' => 'Pedido Cancelado',
    'em_preparo' => 'Em Preparo',
    'entregue' => 'Pedido Entregue',
    'order_confirmed' => 'Pedido Confirmado',
    'pagamento_aprovado' => 'Pagamento Aprovado',
    'saiu_para_entrega' => 'Saiu para Entrega',
    'order_ready' => 'Pronto para Entrega',
    'aguardando_pagamento' => 'Aguardando Pagamento',
    'aguardando_revisao' => 'Aguardando Revisão',
    'entregando' => 'Entregando',
    'pago_confirmado' => 'Pago/Confirmado',
    'confirmado' => 'Confirmado',
    'pending' => 'Pendente',
    'confirmed' => 'Confirmado',
    'preparing' => 'Preparando',
    'ready' => 'Pronto',
    'delivered' => 'Entregue',
    'cancelled' => 'Cancelado',
];
@endphp

@section('content')
@if (!function_exists('currentClientHasFeature') || currentClientHasFeature('whatsapp'))
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Integração WhatsApp</h1>
        <p class="text-muted-foreground">Configure mensagens automáticas via WhatsApp</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if(session('ok'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">{{ session('error') }}</div>
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


    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Instâncias</p>
                        <p class="text-2xl font-bold">{{ $stats['total_instances'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smartphone h-8 w-8 text-primary">
                        <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
                        <path d="M12 18h.01"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Conectadas</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['connected_instances'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle h-8 w-8 text-green-500">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Status Configurados</p>
                        <p class="text-2xl font-bold">{{ $stats['total_statuses'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-checks h-8 w-8 text-primary">
                        <path d="m3 17 2 2 4-4"></path>
                        <path d="M3 7l2 2 4-4"></path>
                        <path d="M13 6h8"></path>
                        <path d="M13 12h8"></path>
                        <path d="M13 18h8"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Notificações Ativas</p>
                        <p class="text-2xl font-bold">{{ $stats['statuses_with_notifications'] ?? 0 }}</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell h-8 w-8 text-primary">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div dir="ltr" data-orientation="horizontal" class="space-y-4">
        <div role="tablist" aria-orientation="horizontal" class="h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground grid w-full grid-cols-3">
            <button type="button" role="tab" data-tab="settings" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 active">Configurações</button>
            <button type="button" role="tab" data-tab="templates" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Templates</button>
            <button type="button" role="tab" data-tab="notifications" class="tab-button inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Notificações</button>
        </div>

        <div data-tab-content="settings" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <!-- Seção: Instâncias WhatsApp -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm mb-6">
                <div class="flex flex-col space-y-1.5 p-6 border-b">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">Instâncias WhatsApp</h3>
                            <p class="text-sm text-muted-foreground mt-1">Gerencie suas conexões do WhatsApp</p>
                        </div>
                        @if(count($availableUrls ?? []) > 0)
                        <button onclick="openNewInstanceModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium transition-all">
                            <i data-lucide="plus" class="h-4 w-4"></i>
                            Nova Instância
                        </button>
                        @endif
                    </div>
                </div>
                <div class="p-6">
                    @if(count($instances ?? []) > 0)
                        <div class="space-y-4">
                            @foreach($instances as $instance)
                            <div class="rounded-lg border p-4 hover:shadow-md transition-all {{ $instance->live_status === 'connected' ? 'bg-gradient-to-r from-white to-green-50 border-green-200' : 'bg-gray-50' }}">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $instance->live_status === 'connected' ? 'bg-green-500' : 'bg-gray-400' }}">
                                            <i data-lucide="smartphone" class="h-6 w-6 text-white"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold">{{ $instance->name }}</h4>
                                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                                @if($instance->live_phone)
                                                <span class="flex items-center gap-1">
                                                    <i data-lucide="phone" class="h-3 w-3"></i>
                                                    {{ preg_replace('/^55(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $instance->live_phone) }}
                                                </span>
                                                <span>•</span>
                                                @endif
                                                <span class="truncate max-w-[200px]" title="{{ $instance->api_url }}">{{ parse_url($instance->api_url, PHP_URL_HOST) }}</span>
                                            </div>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold mt-1 {{ $instance->live_status === 'connected' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1 {{ $instance->live_status === 'connected' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                                {{ $instance->live_status === 'connected' ? 'Conectado' : 'Desconectado' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="checkInstanceStatus({{ $instance->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border hover:bg-gray-100 text-sm font-medium transition-colors" title="Atualizar status">
                                            <i data-lucide="refresh-cw" class="h-4 w-4" id="refresh-icon-{{ $instance->id }}"></i>
                                        </button>
                                        @if($instance->live_status === 'connected')
                                        <button onclick="if(confirm('Desconectar esta instância?')) disconnectInstance({{ $instance->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-red-100 text-red-700 hover:bg-red-200 text-sm font-medium transition-colors">
                                            <i data-lucide="unlink" class="h-4 w-4"></i>
                                            Desconectar
                                        </button>
                                        @else
                                        <button onclick="openConnectModal({{ $instance->id }}, '{{ $instance->name }}', '{{ $instance->phone_number ?? '' }}')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-green-600 text-white hover:bg-green-700 text-sm font-medium transition-colors">
                                            <i data-lucide="link" class="h-4 w-4"></i>
                                            Conectar
                                        </button>
                                        @endif
                                        <button onclick="if(confirm('Remover esta instância permanentemente?')) deleteInstance({{ $instance->id }})" class="inline-flex items-center gap-1 px-2 py-1.5 rounded-md text-red-600 hover:bg-red-50 text-sm font-medium transition-colors" title="Remover">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed p-8 text-center">
                            <i data-lucide="smartphone" class="h-12 w-12 mx-auto mb-3 text-gray-300"></i>
                            <h4 class="font-semibold mb-1">Nenhuma instância configurada</h4>
                            <p class="text-sm text-muted-foreground mb-4">Adicione uma instância WhatsApp para enviar notificações automáticas</p>
                            @if(count($availableUrls ?? []) > 0)
                            <button onclick="openNewInstanceModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium">
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                Adicionar Instância
                            </button>
                            @else
                            <p class="text-sm text-amber-600">Nenhuma instância disponível no momento. Entre em contato com o suporte.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Seção: Configurações de Notificações -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                            <i data-lucide="settings" class="h-5 w-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold leading-none tracking-tight">Configurações de Notificações</h3>
                            <p class="text-sm text-muted-foreground mt-1">Números para notificações do admin</p>
                        </div>
                    </div>
                </div>
                <form action="{{ route('dashboard.settings.whatsapp.admin-notification.save') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="text-sm font-medium leading-none flex items-center gap-2">
                                <i data-lucide="bell" class="h-4 w-4 text-orange-500"></i>
                                Número para Notificações de Admin
                            </label>
                            <input type="text" name="notificacao_whatsapp" id="notificacao_whatsapp" value="{{ old('notificacao_whatsapp', $settings->notificacao_whatsapp ?? '') }}" class="flex h-12 w-full rounded-md border border-input bg-background px-4 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2" placeholder="(71) 99999-9999" oninput="formatPhone(this)">
                            <p class="text-xs text-muted-foreground">Este número receberá alertas quando "Notificar Admin" estiver ativado.</p>
                        </div>
                        <div class="space-y-3">
                            <label class="text-sm font-medium leading-none flex items-center gap-2">
                                <i data-lucide="phone" class="h-4 w-4 text-green-500"></i>
                                Número de Atendimento da Loja
                            </label>
                            <input type="text" name="notificacao_whatsapp_confirmacao" id="notificacao_whatsapp_confirmacao" value="{{ old('notificacao_whatsapp_confirmacao', $settings->notificacao_whatsapp_confirmacao ?? '') }}" class="flex h-12 w-full rounded-md border border-input bg-background px-4 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2" placeholder="(71) 99999-9999" oninput="formatPhone(this)">
                            <p class="text-xs text-muted-foreground">Número principal para clientes fazerem pedidos e tirarem dúvidas.</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end pt-4 border-t">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8 transition-colors">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div data-tab-content="templates" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6 border-b">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">Gerenciar Templates</h3>
                            <p class="text-sm text-muted-foreground mt-1">Crie e edite modelos de mensagens para suas notificações automáticas</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-sm text-muted-foreground">
                                <span class="font-semibold text-lg text-primary">{{ count($templates) }}</span> templates
                            </div>
                            <button onclick="openTemplateModalInTab()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium transition-all">
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                Novo Template
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    @if(count($templates) > 0)
                        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($templates as $template)
                                <div class="rounded-lg border p-4 hover:shadow-md transition-all flex flex-col {{ $template->active ? 'bg-gradient-to-br from-white to-green-50 border-green-200' : 'bg-gray-50' }}">
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between gap-2 mb-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <i data-lucide="message-square" class="h-4 w-4 flex-shrink-0 {{ $template->active ? 'text-green-600' : 'text-gray-400' }}"></i>
                                                    <span class="font-semibold text-sm truncate">{{ $templateLabels[$template->slug] ?? ucwords(str_replace('_', ' ', $template->slug)) }}</span>
                                                </div>
                                                @if($template->active)
                                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800">
                                                        <i data-lucide="check-circle" class="h-3 w-3 mr-1"></i>
                                                        Ativo
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-muted-foreground whitespace-pre-wrap line-clamp-2">{{ Str::limit($template->content, 100) }}</p>
                                    </div>
                                    <div class="pt-3 mt-3 border-t">
                                        <div class="flex items-center justify-between">
                                            <div class="text-xs text-muted-foreground">
                                                @php
                                                    $usedBy = $statuses->where('whatsapp_template_id', $template->id);
                                                    $usedByCount = $usedBy->count();
                                                @endphp
                                                @if($usedByCount > 0)
                                                    <span class="text-green-600 font-medium">{{ $usedByCount }} status</span>
                                                @else
                                                    <span class="text-gray-400">Não utilizado</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button onclick="editTemplateInTab({{ $template->id }})" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-primary hover:bg-primary/10 rounded transition-colors">
                                                    <i data-lucide="edit" class="h-3 w-3"></i>
                                                    Editar
                                                </button>
                                                <button onclick="previewTemplate({{ $template->id }}, '{{ addslashes($template->content) }}')" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                                    <i data-lucide="eye" class="h-3 w-3"></i>
                                                    Prévia
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border p-12 text-center">
                            <i data-lucide="message-square-plus" class="h-16 w-16 mx-auto mb-4 text-gray-300"></i>
                            <h4 class="text-lg font-semibold mb-2">Nenhum template cadastrado</h4>
                            <p class="text-sm text-muted-foreground mb-4">Crie templates de mensagens para automatizar suas notificações</p>
                            <button onclick="openTemplateModalInTab()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium">
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                Criar Primeiro Template
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div data-tab-content="notifications" class="tab-content mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 hidden">
            <!-- Configurações de Notificações do Admin -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm mb-6">
                <div class="flex flex-col space-y-1.5 p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <i data-lucide="settings" class="h-6 w-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-semibold leading-none tracking-tight">Configurações de Notificações</h3>
                            <p class="text-sm text-muted-foreground mt-1">Configure os números que receberão notificações automáticas</p>
                        </div>
                    </div>
                </div>
                <form action="{{ route('dashboard.settings.whatsapp.admin-notification.save') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <label class="text-sm font-medium leading-none flex items-center gap-2">
                                <i data-lucide="bell" class="h-4 w-4 text-orange-500"></i>
                                Número para Notificações de Admin
                            </label>
                            <input 
                                type="text" 
                                name="notificacao_whatsapp" 
                                id="notificacao_whatsapp" 
                                value="{{ old('notificacao_whatsapp', $settings->notificacao_whatsapp ?? '') }}" 
                                class="flex h-12 w-full rounded-md border border-input bg-background px-4 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2" 
                                placeholder="(71) 99999-9999"
                                oninput="formatPhone(this)"
                            >
                            <p class="text-xs text-muted-foreground">Este número receberá alertas quando "Notificar Admin" estiver ativado nos status dos pedidos.</p>
                        </div>
                        
                        <div class="space-y-3">
                            <label class="text-sm font-medium leading-none flex items-center gap-2">
                                <i data-lucide="phone" class="h-4 w-4 text-green-500"></i>
                                Número de Atendimento da Loja
                            </label>
                            <input 
                                type="text" 
                                name="notificacao_whatsapp_confirmacao" 
                                id="notificacao_whatsapp_confirmacao" 
                                value="{{ old('notificacao_whatsapp_confirmacao', $settings->notificacao_whatsapp_confirmacao ?? '') }}" 
                                class="flex h-12 w-full rounded-md border border-input bg-background px-4 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2" 
                                placeholder="(71) 99999-9999"
                                oninput="formatPhone(this)"
                            >
                            <p class="text-xs text-muted-foreground">Número principal para clientes fazerem pedidos, receberem notificações e tirarem dúvidas.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end pt-4 border-t">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8 transition-colors">
                            <i data-lucide="save" class="h-4 w-4"></i>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Notificações por Status -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6 border-b">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                <i data-lucide="bell-ring" class="h-5 w-5 text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold leading-none tracking-tight">Notificações por Status</h3>
                                <p class="text-sm text-muted-foreground mt-1">Ative as notificações automáticas para cada status do pedido</p>
                            </div>
                        </div>
                        <div class="text-sm text-muted-foreground sm:text-right">
                            <span class="font-semibold text-lg text-green-600">{{ $statuses->where('notify_customer', 1)->count() }}</span> ativos
                        </div>
                    </div>
                </div>
                <form action="{{ route('dashboard.settings.whatsapp.notifications.save') }}" method="POST" class="p-6">
                    @csrf
                    @if($statuses->count() > 0)
                        <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($statuses as $status)
                                <div class="rounded-lg border p-4 hover:shadow-md transition-all flex flex-col {{ $status->notify_customer || $status->notify_admin ? 'bg-gradient-to-br from-white to-green-50 border-green-200' : 'bg-gray-50' }}">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="package" class="h-4 w-4 text-primary"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm truncate">{{ $status->name }}</p>
                                                @if($status->template_slug)
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs text-blue-600 truncate">{{ $templateLabels[$status->template_slug] ?? ucwords(str_replace('_', ' ', $status->template_slug)) }}</span>
                                                        @php
                                                            $tplId = $templates->firstWhere('slug', $status->template_slug)?->id;
                                                        @endphp
                                                        @if($tplId)
                                                            <button type="button" onclick="editTemplateInTab({{ $tplId }})" class="p-0.5 hover:bg-blue-100 rounded flex-shrink-0" title="Editar template">
                                                                <i data-lucide="edit-2" class="h-3 w-3 text-blue-500"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="flex items-center gap-1">
                                                        <p class="text-xs text-amber-600">
                                                            <i data-lucide="alert-circle" class="h-3 w-3 inline"></i>
                                                            Nenhum template
                                                        </p>
                                                        <button type="button" onclick="openTemplateModalInTab('{{ \Illuminate\Support\Str::slug($status->name, '_') }}')" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium text-primary hover:bg-primary/10">
                                                            <i data-lucide="plus" class="h-3 w-3"></i>
                                                            Criar
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-md border transition-all {{ $status->notify_customer ? 'border-green-300 bg-green-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                                <input type="checkbox" name="notifications[{{ $status->id }}][customer]" value="1" {{ $status->notify_customer ? 'checked' : '' }} class="w-4 h-4 text-green-600 rounded focus:ring-green-500">
                                                <span class="text-xs font-medium">Notificar Cliente</span>
                                            </label>
                                            
                                            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-md border transition-all {{ $status->notify_admin ? 'border-orange-300 bg-orange-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                                <input type="checkbox" name="notifications[{{ $status->id }}][admin]" value="1" {{ $status->notify_admin ? 'checked' : '' }} class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500">
                                                <span class="text-xs font-medium">Notificar Admin</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="flex items-center justify-end gap-3 pt-4 mt-4 border-t">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-6 transition-colors">
                                <i data-lucide="save" class="h-4 w-4"></i>
                                Salvar Notificações
                            </button>
                        </div>
                    @else
                        <div class="rounded-lg border p-8 text-center">
                            <i data-lucide="inbox" class="h-12 w-12 mx-auto mb-3 text-gray-300"></i>
                            <h4 class="font-semibold mb-1">Nenhum status cadastrado</h4>
                            <p class="text-sm text-muted-foreground">Configure os status primeiro</p>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@else
    <div class="max-w-3xl mx-auto mt-10">
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="lock" class="h-5 w-5 text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl sm:text-2xl font-semibold leading-tight text-amber-900">
                            Integração WhatsApp não incluída no seu plano atual
                        </h2>
                        <p class="text-sm text-amber-900/80 mt-1">
                            Para usar notificações automáticas, templates e integração com WhatsApp, é necessário contratar um pacote de WhatsApp ou mudar para um plano que inclua esse recurso.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col sm:items-end gap-2 mt-2 sm:mt-0">
                    <a href="{{ route('dashboard.subscription.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-md bg-amber-600 text-white px-4 py-2 text-sm font-medium hover:bg-amber-700 transition-colors">
                        <i data-lucide="crown" class="h-4 w-4"></i>
                        Ver planos e pacotes
                    </a>
                    <p class="text-[11px] text-amber-900/80 sm:text-right">
                        Você pode adicionar serviços extras além do plano atual a qualquer momento.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modal de Criação/Edição de Template (Reutilizável) -->
<div id="templateModalTab" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden animate-in zoom-in-95 duration-200">
    <div class="flex items-center justify-between p-6 border-b">
      <div>
        <h3 class="text-2xl font-semibold" id="modalTabTitle">Novo Template</h3>
        <p class="text-sm text-muted-foreground mt-1">Crie ou edite um modelo de mensagem</p>
      </div>
      <button onclick="closeTemplateModalInTab()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>
    
    <form action="{{ route('dashboard.settings.status-templates.template.save') }}" method="POST" class="overflow-y-auto" style="max-height: calc(90vh - 180px);">
      @csrf
      <input type="hidden" name="id" id="modal_tab_tpl_id" value="">
      
      <div class="p-6 space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="text-sm font-medium flex items-center gap-2">
              <i data-lucide="tag" class="h-4 w-4"></i>
              Identificador do Template
            </label>
            <input name="slug" id="modal_tab_tpl_slug" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20" placeholder="ex: pedido_confirmado" required>
            <p class="text-xs text-muted-foreground">Use apenas letras minúsculas, números e underline</p>
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium">Status do Template</label>
            <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
              <input type="checkbox" name="active" id="modal_tab_tpl_active" value="1" checked class="rounded text-primary focus:ring-primary w-5 h-5">
              <div>
                <span class="text-sm font-medium">Template Ativo</span>
                <p class="text-xs text-muted-foreground">Template estará disponível para uso</p>
              </div>
            </label>
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium flex items-center gap-2">
            <i data-lucide="message-square" class="h-4 w-4"></i>
            Conteúdo da Mensagem
          </label>
          <textarea name="content" id="modal_tab_tpl_content" rows="8" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 font-mono" placeholder="Olá {nome}!\n\nSeu pedido #{pedido} foi confirmado.\nTotal: {total}\n\nObrigado pela preferência!" required></textarea>
          <div class="flex items-center justify-between">
            <p class="text-xs text-muted-foreground">Use as variáveis listadas abaixo para personalizar</p>
            <span id="charCountTab" class="text-xs text-muted-foreground">0 caracteres</span>
          </div>
        </div>
        
          <div class="p-4 bg-gray-50 rounded-lg border">
          <p class="text-sm font-medium mb-3">Variáveis Disponíveis - Clique para inserir:</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <button type="button" onclick="insertVariableInTab('{nome}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Nome do cliente">{nome}</button>
            <button type="button" onclick="insertVariableInTab('{pedido}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Número do pedido">{pedido}</button>
            <button type="button" onclick="insertVariableInTab('{total}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Valor total">{total}</button>
            <button type="button" onclick="insertVariableInTab('{status}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Status do pedido">{status}</button>
            <button type="button" onclick="insertVariableInTab('{link}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Link do WhatsApp">{link}</button>
            <button type="button" onclick="insertVariableInTab('{data}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Data do pedido">{data}</button>
            <button type="button" onclick="insertVariableInTab('{horario}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Horário">{horario}</button>
            <button type="button" onclick="insertVariableInTab('{endereco}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Endereço de entrega">{endereco}</button>
            <button type="button" onclick="insertVariableInTab('{pagamento}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors" title="Forma de pagamento">{pagamento}</button>
          </div>
        </div>
        
        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
          <p class="text-sm font-medium mb-2 text-green-900">Prévia em Tempo Real:</p>
          <div class="bg-white rounded-lg p-3 text-sm whitespace-pre-wrap font-mono" id="modalTabPreview">Digite algo para ver a prévia...</div>
        </div>
      </div>
      
      <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
        <button type="button" onclick="closeTemplateModalInTab()" class="px-4 py-2 rounded-md border hover:bg-gray-100 text-sm font-medium transition-colors">
          Cancelar
        </button>
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium transition-colors">
          <i data-lucide="save" class="h-4 w-4"></i>
          Salvar Template
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Nova Instância WhatsApp -->
<div id="newInstanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-2xl max-w-lg w-full animate-in zoom-in-95 duration-200">
    <div class="flex items-center justify-between p-6 border-b">
      <div>
        <h3 class="text-xl font-semibold">Nova Instância WhatsApp</h3>
        <p class="text-sm text-muted-foreground mt-1">Configure sua conexão do WhatsApp</p>
      </div>
      <button onclick="closeNewInstanceModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>
    <form action="{{ route('dashboard.settings.whatsapp.instances.store') }}" method="POST" class="p-6 space-y-4">
      @csrf
      <div class="space-y-2">
        <label class="text-sm font-medium">Nome da Instância <span class="text-red-500">*</span></label>
        <input type="text" name="name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Ex: Principal, Marketing, Vendas" required>
        <p class="text-xs text-muted-foreground">Nome para identificar esta conexão</p>
      </div>
      <div class="space-y-2">
        <label class="text-sm font-medium">Número do WhatsApp <span class="text-red-500">*</span></label>
        <input type="text" name="phone_number" id="new_instance_phone" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="(71) 99999-9999" value="{{ $settings->notificacao_whatsapp_confirmacao ?? $settings->notificacao_whatsapp ?? '' }}" oninput="formatPhone(this)" required>
        <p class="text-xs text-muted-foreground">Número que será conectado ao WhatsApp Business</p>
      </div>
      <div class="flex items-center justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="closeNewInstanceModal()" class="px-4 py-2 rounded-md border hover:bg-gray-100 text-sm font-medium">Cancelar</button>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium">
          <i data-lucide="plus" class="h-4 w-4"></i>
          Criar Instância
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Conectar Instância -->
<div id="connectInstanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-2xl max-w-lg w-full animate-in zoom-in-95 duration-200">
    <div class="flex items-center justify-between p-6 border-b">
      <div>
        <h3 class="text-xl font-semibold">Conectar WhatsApp</h3>
        <p class="text-sm text-muted-foreground mt-1" id="connectModalSubtitle">Informe o número para gerar o código</p>
      </div>
      <button onclick="closeConnectModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>
    <div class="p-6 space-y-4">
      <input type="hidden" id="connect_instance_id">
      <div class="space-y-2">
        <label class="text-sm font-medium">Número do WhatsApp</label>
        <input type="text" id="connect_phone" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="(71) 99999-9999" oninput="formatPhone(this)">
        <p class="text-xs text-muted-foreground">Número com DDD que será conectado</p>
      </div>
      <div id="pairingCodeResult" class="hidden p-4 bg-green-50 rounded-lg border border-green-200">
        <p class="text-sm font-medium text-green-900 mb-2">Código de Pareamento:</p>
        <p class="text-3xl font-mono font-bold text-green-700 tracking-widest" id="pairingCodeValue"></p>
        <p class="text-xs text-green-700 mt-2">Digite este código no seu WhatsApp: Configurações > Aparelhos Conectados > Conectar dispositivo</p>
      </div>
      <div id="connectError" class="hidden p-4 bg-red-50 rounded-lg border border-red-200">
        <p class="text-sm text-red-700" id="connectErrorMessage"></p>
      </div>
      <div class="flex items-center justify-end gap-3 pt-4 border-t">
        <button type="button" onclick="closeConnectModal()" class="px-4 py-2 rounded-md border hover:bg-gray-100 text-sm font-medium">Fechar</button>
        <button type="button" onclick="doConnectInstance()" id="connectBtn" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700 text-sm font-medium">
          <i data-lucide="link" class="h-4 w-4"></i>
          Gerar Código
        </button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
.tab-button.active {
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active state from all tabs
            tabs.forEach(t => {
                t.classList.remove('active');
            });
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            const selectedContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
            }
            
            // Add active state to clicked tab
            this.classList.add('active');
        });
    });
    
    // Event listeners para modal da aba Templates
    const modalTabContent = document.getElementById('modal_tab_tpl_content');
    if (modalTabContent) {
        modalTabContent.addEventListener('input', function() {
            updateModalTabPreview();
            updateCharCountTab();
        });
    }
    
    // Fechar modal ao clicar fora
    const modalTab = document.getElementById('templateModalTab');
    if (modalTab) {
        modalTab.addEventListener('click', function(e) {
            if (e.target === modalTab) {
                closeTemplateModalInTab();
            }
        });
    }
    
    // Tecla ESC para fechar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modalTab = document.getElementById('templateModalTab');
            if (modalTab && !modalTab.classList.contains('hidden')) {
                closeTemplateModalInTab();
            }
        }
    });
    
    // Check connection status on page load
    checkConnectionStatus();
    
    // Formatar telefones existentes ao carregar
    const phoneInputs = document.querySelectorAll('#notificacao_whatsapp, #notificacao_whatsapp_confirmacao');
    phoneInputs.forEach(input => {
        if (input.value) {
            formatPhoneFromValue(input, input.value);
        }
    });
});

function previewTemplate(id, content) {
    // Criar modal de preview
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-2xl max-w-2xl w-full animate-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between p-6 border-b">
                <div>
                    <h3 class="text-2xl font-semibold">Prévia do Template</h3>
                    <p class="text-sm text-muted-foreground mt-1">Visualize como a mensagem será enviada</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 shadow-lg">
                    <div class="bg-white rounded-xl p-4 shadow-sm">
                        <div class="flex items-center gap-3 mb-4 pb-3 border-b">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                                <i data-lucide="store" class="h-5 w-5 text-white"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-sm">{{ config('app.name', 'Sua Loja') }}</div>
                                <div class="text-xs text-muted-foreground">Online</div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="inline-block bg-green-500 text-white rounded-lg rounded-tl-none px-4 py-2 max-w-full">
                                <div class="text-xs opacity-75 mb-1">Pedido #123</div>
                                <div class="whitespace-pre-wrap text-sm">${renderTemplatePreview(content)}</div>
                                <div class="text-xs opacity-75 mt-2 text-right">${new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 rounded-md border hover:bg-gray-100 text-sm font-medium transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Reinicializar Lucide icons no modal
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Fechar ao clicar fora
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Funções do Modal da Aba Templates
function openTemplateModalInTab(slug = '') {
    document.getElementById('templateModalTab').classList.remove('hidden');
    document.getElementById('templateModalTab').classList.add('flex');
    document.getElementById('modalTabTitle').textContent = 'Novo Template';
    document.getElementById('modal_tab_tpl_id').value = '';
    document.getElementById('modal_tab_tpl_slug').value = slug || '';
    document.getElementById('modal_tab_tpl_content').value = '';
    document.getElementById('modal_tab_tpl_active').checked = true;
    updateModalTabPreview();
    updateCharCountTab();
    // Reinicializar ícones
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function closeTemplateModalInTab() {
    document.getElementById('templateModalTab').classList.add('hidden');
    document.getElementById('templateModalTab').classList.remove('flex');
}

function insertVariableInTab(variable) {
    const textarea = document.getElementById('modal_tab_tpl_content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const before = text.substring(0, start);
    const after = text.substring(end, text.length);
    
    textarea.value = before + variable + after;
    textarea.selectionStart = textarea.selectionEnd = start + variable.length;
    textarea.focus();
    
    updateModalTabPreview();
    updateCharCountTab();
}

function updateCharCountTab() {
    const content = document.getElementById('modal_tab_tpl_content')?.value || '';
    const count = content.length;
    const charCountEl = document.getElementById('charCountTab');
    if (charCountEl) {
        charCountEl.textContent = count + ' caracteres';
        if (count > 1000) {
            charCountEl.classList.add('text-red-600', 'font-semibold');
        } else {
            charCountEl.classList.remove('text-red-600', 'font-semibold');
        }
    }
}

function updateModalTabPreview() {
    const content = document.getElementById('modal_tab_tpl_content')?.value || '';
    const preview = renderTemplatePreview(content);
    
    const previewEl = document.getElementById('modalTabPreview');
    if (previewEl) {
        previewEl.textContent = preview || 'Digite algo para ver a prévia...';
    }
}

async function editTemplateInTab(id) {
    try {
        const res = await fetch('{{ route('dashboard.settings.status-templates.template.get', ['id' => '__ID__']) }}'.replace('__ID__', id), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!res.ok) {
            throw new Error('Erro HTTP: ' + res.status);
        }
        
        const data = await res.json();
        
        if (data && !data.error) {
            document.getElementById('modal_tab_tpl_id').value = data.id;
            document.getElementById('modal_tab_tpl_slug').value = data.slug || '';
            document.getElementById('modal_tab_tpl_content').value = data.content || '';
            document.getElementById('modal_tab_tpl_active').checked = !!(data.active);
            document.getElementById('modalTabTitle').textContent = 'Editar Template';
            
            updateModalTabPreview();
            updateCharCountTab();
            
            // Abrir modal
            document.getElementById('templateModalTab').classList.remove('hidden');
            document.getElementById('templateModalTab').classList.add('flex');
            
            // Reinicializar ícones
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        } else {
            throw new Error(data.error || 'Template não encontrado');
        }
    } catch (e) {
        console.error('Erro ao carregar template:', e);
        alert('Erro ao carregar template: ' + e.message);
    }
}

function renderTemplatePreview(content) {
    return content
        .replace(/{nome}/g, 'Maria Silva')
        .replace(/{pedido}/g, '123')
        .replace(/{total}/g, 'R$ 99,90')
        .replace(/{status}/g, 'Confirmado')
        .replace(/{link}/g, 'https://wa.me/5571999999999')
        .replace(/{data}/g, new Date().toLocaleDateString('pt-BR'))
        .replace(/{horario}/g, new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}))
        .replace(/{endereco}/g, 'Rua Exemplo, 123 - Bairro - Cidade/UF')
        .replace(/{pagamento}/g, 'Cartão de Crédito');
}

// Função para formatar telefone brasileiro
function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    
    // Limita a 13 dígitos (55 + DDD + 9 dígitos)
    if (value.length > 13) {
        value = value.substring(0, 13);
    }
    
    let formatted = '';
    
    if (value.length === 0) {
        formatted = '';
    } else if (value.length <= 2) {
        // Apenas DDD
        formatted = '(' + value;
    } else if (value.length <= 7) {
        // DDD + primeiro bloco
        formatted = '(' + value.substring(0, 2) + ') ' + value.substring(2);
    } else if (value.length <= 11) {
        // DDD + telefone completo
        formatted = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7);
    } else {
        // Com código do país (remove o 55 da exibição)
        const ddd = value.substring(2, 4);
        const first = value.substring(4, 9);
        const last = value.substring(9);
        formatted = '(' + ddd + ') ' + first + '-' + last;
    }
    
    input.value = formatted;
}

// Função para formatar telefone a partir de valor internacional
function formatPhoneFromValue(input, rawValue) {
    let value = rawValue.replace(/\D/g, '');
    
    // Se começa com 55, remove para formatar
    if (value.startsWith('55') && value.length > 11) {
        const ddd = value.substring(2, 4);
        const first = value.substring(4, 9);
        const last = value.substring(9);
        input.value = '(' + ddd + ') ' + first + '-' + last;
    } else if (value.length >= 10) {
        const ddd = value.substring(0, 2);
        const first = value.substring(2, 7);
        const last = value.substring(7);
        input.value = '(' + ddd + ') ' + first + '-' + last;
    }
}

// Alternar exibição das opções avançadas do template
function toggleTemplateAdvanced() {
    const container = document.getElementById('templateAdvancedFields');
    if (container) {
        container.classList.toggle('hidden');
    }
}


function checkConnectionStatus() {
    const indicator = document.getElementById('connection-indicator');
    const statusText = document.getElementById('connection-status-text');
    const refreshIcon = document.getElementById('refresh-icon');
    const phoneContainer = document.getElementById('connection-phone-text');
    const phoneValue = document.getElementById('connection-phone-value');
    
    if (refreshIcon) {
        refreshIcon.classList.add('animate-spin');
    }
    
    // Timeout de 15 segundos para evitar travamento
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000);
    
    fetch('/settings/whatsapp/status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        signal: controller.signal
    })
    .then(response => response.json())
    .then(data => {
        clearTimeout(timeoutId);
        if (indicator) {
            if (data.connected) {
                indicator.style.backgroundColor = '#22c55e';
                statusText.innerHTML = '<span class="text-green-600">Conectado</span>';
            } else {
                indicator.style.backgroundColor = '#ef4444';
                statusText.innerHTML = '<span class="text-red-600">Desconectado</span>';
                if (data.message) {
                    console.log('Status WhatsApp:', data.message);
                }
            }
        }

        if (phoneContainer && phoneValue) {
            if (data.phone) {
                phoneValue.textContent = data.phone;
                phoneContainer.classList.remove('hidden');
            } else {
                phoneContainer.classList.add('hidden');
            }
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        console.error('Erro ao verificar status:', error);
        if (indicator) {
            indicator.style.backgroundColor = '#eab308';
            if (error.name === 'AbortError') {
                statusText.innerHTML = '<span class="text-yellow-600">Timeout - API não responde</span>';
            } else {
                statusText.innerHTML = '<span class="text-yellow-600">Erro ao verificar</span>';
            }
        }
    })
    .finally(() => {
        if (refreshIcon) {
            refreshIcon.classList.remove('animate-spin');
        }
    });
}

function disconnectInstance() {
    // Verificar se o CSRF token existe
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        alert('Erro: Token CSRF não encontrado. Recarregue a página.');
        return;
    }
    
    fetch('/settings/whatsapp/disconnect', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erro ao desconectar: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro ao desconectar:', error);
        alert('Erro ao desconectar instância');
    });
}

function connectInstance() {
    // Mostrar loading no botão
    const btn = event?.target;
    const originalText = btn?.innerHTML || '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i> Conectando...';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    
    // Verificar se o CSRF token existe
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        alert('Erro: Token CSRF não encontrado. Recarregue a página.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
        return;
    }
    
    fetch('/settings/whatsapp/connect', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Verificar se tem QR Code ou Pairing Code
            if (data.qr_code) {
                alert('QR Code gerado! Escaneie com seu WhatsApp.');
            } else if (data.pairing_code) {
                alert('Código de pareamento: ' + data.pairing_code);
            } else {
                alert(data.message || 'Conexão iniciada! Aguarde...');
            }
            // Recarregar após 2 segundos para verificar status
            setTimeout(() => window.location.reload(), 2000);
        } else {
            alert('Erro ao conectar: ' + (data.message || 'Erro desconhecido'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Erro ao conectar:', error);
        alert('Erro ao conectar instância. Verifique se a URL da API está correta.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}

// ========== FUNÇÕES PARA MÚLTIPLAS INSTÂNCIAS ==========

// Modal: Nova Instância
function openNewInstanceModal() {
    document.getElementById('newInstanceModal').classList.remove('hidden');
    document.getElementById('newInstanceModal').classList.add('flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function closeNewInstanceModal() {
    document.getElementById('newInstanceModal').classList.add('hidden');
    document.getElementById('newInstanceModal').classList.remove('flex');
}

// Modal: Conectar Instância
function openConnectModal(instanceId, instanceName, phone) {
    document.getElementById('connect_instance_id').value = instanceId;
    document.getElementById('connect_phone').value = phone || '';
    document.getElementById('connectModalSubtitle').textContent = 'Instância: ' + instanceName;
    document.getElementById('pairingCodeResult').classList.add('hidden');
    document.getElementById('connectError').classList.add('hidden');
    document.getElementById('connectBtn').disabled = false;
    
    document.getElementById('connectInstanceModal').classList.remove('hidden');
    document.getElementById('connectInstanceModal').classList.add('flex');
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    // Formatar telefone se existir
    const phoneInput = document.getElementById('connect_phone');
    if (phoneInput.value) {
        formatPhoneFromValue(phoneInput, phoneInput.value);
    }
}

function closeConnectModal() {
    document.getElementById('connectInstanceModal').classList.add('hidden');
    document.getElementById('connectInstanceModal').classList.remove('flex');
}

// Conectar instância específica
function doConnectInstance() {
    const instanceId = document.getElementById('connect_instance_id').value;
    const phoneInput = document.getElementById('connect_phone');
    const phone = phoneInput.value.replace(/\D/g, ''); // Só números
    const btn = document.getElementById('connectBtn');
    const pairingResult = document.getElementById('pairingCodeResult');
    const errorDiv = document.getElementById('connectError');
    
    if (!phone || phone.length < 10) {
        errorDiv.classList.remove('hidden');
        document.getElementById('connectErrorMessage').textContent = 'Informe um número válido com DDD';
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="h-4 w-4 animate-spin"></i> Gerando código...';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    pairingResult.classList.add('hidden');
    errorDiv.classList.add('hidden');
    
    // Pegar CSRF token atualizado
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        errorDiv.classList.remove('hidden');
        document.getElementById('connectErrorMessage').textContent = 'Sessão expirada. Recarregue a página.';
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="link" class="h-4 w-4"></i> Gerar Código';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        return;
    }
    
    fetch(`/settings/whatsapp/instances/${instanceId}/connect`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ phone: phone }),
        credentials: 'same-origin'
    })
    .then(response => {
        if (response.status === 419) {
            throw new Error('CSRF token expirado. Recarregue a página.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (data.pairing_code) {
                document.getElementById('pairingCodeValue').textContent = data.pairing_code;
                pairingResult.classList.remove('hidden');
                btn.innerHTML = '<i data-lucide="check" class="h-4 w-4"></i> Código Gerado!';
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
                btn.classList.add('bg-gray-400');
            } else {
                errorDiv.classList.remove('hidden');
                document.getElementById('connectErrorMessage').textContent = data.message || 'Código não retornado. Verifique os logs do servidor.';
            }
        } else {
            errorDiv.classList.remove('hidden');
            document.getElementById('connectErrorMessage').textContent = data.message || 'Erro ao gerar código';
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="link" class="h-4 w-4"></i> Gerar Código';
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    })
    .catch(error => {
        console.error('Erro:', error);
        errorDiv.classList.remove('hidden');
        document.getElementById('connectErrorMessage').textContent = error.message || 'Erro de conexão. Tente novamente.';
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="link" class="h-4 w-4"></i> Gerar Código';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
}

// Verificar status de uma instância específica
function checkInstanceStatus(instanceId) {
    const icon = document.getElementById('refresh-icon-' + instanceId);
    if (icon) icon.classList.add('animate-spin');
    
    fetch(`/settings/whatsapp/instances/${instanceId}/status`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        // Recarregar página para atualizar status
        window.location.reload();
    })
    .catch(() => {
        alert('Erro ao verificar status');
    })
    .finally(() => {
        if (icon) icon.classList.remove('animate-spin');
    });
}

// Desconectar instância específica
function disconnectInstance(instanceId) {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    
    fetch(`/settings/whatsapp/instances/${instanceId}/disconnect`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao desconectar'));
        }
    })
    .catch(error => {
        alert('Erro ao desconectar: ' + error.message);
    });
}

// Remover instância
function deleteInstance(instanceId) {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    
    fetch(`/settings/whatsapp/instances/${instanceId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfMeta.getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro ao remover'));
        }
    })
    .catch(error => {
        alert('Erro ao remover: ' + error.message);
    });
}

// Fechar modais com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewInstanceModal();
        closeConnectModal();
    }
});

// Fechar modais ao clicar fora
document.getElementById('newInstanceModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeNewInstanceModal();
});
document.getElementById('connectInstanceModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeConnectModal();
});
</script>
@endpush
@endsection
