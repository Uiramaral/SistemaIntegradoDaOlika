@extends('dashboard.layouts.app')

@section('title', 'Detalhes do Cliente SaaS - OLIKA Dashboard')

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
                <h1 class="text-3xl font-bold tracking-tight">{{ $saasClient->name }}</h1>
                <p class="text-muted-foreground">Detalhes do cliente SaaS</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Informações Básicas -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-xl font-semibold leading-none tracking-tight">Informações Básicas</h3>
            </div>
            <div class="p-6 pt-0 space-y-4">
                <div>
                    <label class="text-sm font-medium text-muted-foreground">Nome da Empresa</label>
                    <p class="text-sm font-semibold mt-1">{{ $saasClient->name }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-muted-foreground">Slug</label>
                    <p class="text-sm font-semibold mt-1">{{ $saasClient->slug }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-muted-foreground">Plano</label>
                    <p class="mt-1">
                        @if($saasClient->plan === 'ia')
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">WhatsApp</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Básico</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                    <p class="mt-1">
                        @if($saasClient->active)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Ativo</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Inativo</span>
                        @endif
                    </p>
                </div>
                @if($saasClient->whatsapp_phone)
                <div>
                    <label class="text-sm font-medium text-muted-foreground">Telefone WhatsApp</label>
                    <p class="text-sm font-semibold mt-1">{{ $saasClient->whatsapp_phone }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Usuário e Token -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-xl font-semibold leading-none tracking-tight">Acesso e API</h3>
            </div>
            <div class="p-6 pt-0 space-y-4">
                @if($saasClient->users->count() > 0)
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Usuário Principal</label>
                        <p class="text-sm font-semibold mt-1">{{ $saasClient->users->first()->email }}</p>
                    </div>
                @endif

                @if($saasClient->activeApiToken)
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">Token de API</label>
                        <div class="mt-1 flex items-center gap-2">
                            <code class="text-xs bg-muted px-2 py-1 rounded flex-1 truncate">{{ $saasClient->activeApiToken->token }}</code>
                            <button onclick="navigator.clipboard.writeText('{{ $saasClient->activeApiToken->token }}')" class="inline-flex items-center justify-center rounded-md p-1 hover:bg-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                    <path d="M4 16c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Token gerado automaticamente ao criar o cliente</p>
                    </div>
                @endif

                @if($saasClient->instance_url)
                    <div>
                        <label class="text-sm font-medium text-muted-foreground">URL da Instância</label>
                        <p class="text-sm font-semibold mt-1 break-all">{{ $saasClient->instance_url }}</p>
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-muted-foreground">Cadastrado em</label>
                    <p class="text-sm font-semibold mt-1">{{ $saasClient->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

