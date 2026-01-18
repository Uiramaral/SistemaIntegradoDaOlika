@extends('dashboard.layouts.app')

@section('title', 'Novo Cliente SaaS - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.saas-clients.index') }}" class="inline-flex items-center justify-center rounded-md p-2 hover:bg-accent hover:text-accent-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left h-5 w-5">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Novo Cliente SaaS</h1>
                <p class="text-muted-foreground">Cadastre um novo assinante da plataforma</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Formulário -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-xl font-semibold leading-none tracking-tight">Informações do Cliente</h3>
            <p class="text-sm text-muted-foreground">Preencha os dados para criar uma nova conta de assinante</p>
        </div>
        <form action="{{ route('dashboard.saas-clients.store') }}" method="POST" class="p-6 pt-0 space-y-6">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-2">Nome da Empresa *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Ex: Restaurante XYZ">
                    @error('name')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">E-mail de Acesso *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="contato@empresa.com.br">
                    @error('email')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                    <p class="text-xs text-muted-foreground mt-1">Será usado para login no sistema</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-2">Senha *</label>
                    <input type="password" name="password" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="Mínimo 6 caracteres">
                    @error('password')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Confirmar Senha *</label>
                    <input type="password" name="password_confirmation" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium mb-2">Plano *</label>
                    <select name="plan" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <option value="">Selecione um plano</option>
                        <option value="basic" {{ old('plan') == 'basic' ? 'selected' : '' }}>Básico - Vendas, PDV e Cadastros</option>
                        <option value="ia" {{ old('plan') == 'ia' ? 'selected' : '' }}>WhatsApp - Inclui módulo de WhatsApp</option>
                    </select>
                    @error('plan')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                    <p class="text-xs text-muted-foreground mt-1">
                        <strong>Básico:</strong> Vendas, cadastro de itens, PDV e funcionalidades principais<br>
                        <strong>WhatsApp:</strong> Inclui tudo do básico + integração WhatsApp para envio de mensagens
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Telefone WhatsApp (opcional)</label>
                    <input type="text" name="whatsapp_phone" value="{{ old('whatsapp_phone') }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" placeholder="5511999999999">
                    @error('whatsapp_phone')<span class="text-sm text-red-600 mt-1 block">{{ $message }}</span>@enderror
                    <p class="text-xs text-muted-foreground mt-1">Número que receberá notificações de pagamento (se aplicável)</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('dashboard.saas-clients.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                    Cadastrar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

