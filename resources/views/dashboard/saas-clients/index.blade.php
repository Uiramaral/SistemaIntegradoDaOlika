@extends('dashboard.layouts.app')

@section('title', 'Clientes SaaS - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Clientes SaaS</h1>
            <p class="text-muted-foreground">Gerenciar assinantes da plataforma</p>
        </div>
        <a href="{{ route('dashboard.saas-clients.create') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                <path d="M5 12h14"></path>
                <path d="M12 5v14"></path>
            </svg>
            Novo Cliente SaaS
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <!-- Lista de Clientes -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            @if($clients->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-semibold">Nome</th>
                                <th class="text-left py-3 px-4 font-semibold">E-mail</th>
                                <th class="text-left py-3 px-4 font-semibold">Plano</th>
                                <th class="text-left py-3 px-4 font-semibold">Status</th>
                                <th class="text-left py-3 px-4 font-semibold">Cadastrado em</th>
                                <th class="text-right py-3 px-4 font-semibold">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                                <tr class="border-b hover:bg-muted/50">
                                    <td class="py-3 px-4">
                                        <div class="font-medium">{{ $client->name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ $client->slug }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $client->users->first()->email ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($client->plan === 'ia')
                                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">WhatsApp</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Básico</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($client->active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Ativo</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-muted-foreground">
                                        {{ $client->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('dashboard.saas-clients.show', $client) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                                            Ver Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $clients->links() }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center gap-3 py-12 text-center text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users text-muted-foreground">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">Nenhum cliente SaaS cadastrado</p>
                        <p class="text-xs mt-1">Comece cadastrando o primeiro assinante da plataforma</p>
                    </div>
                    <a href="{{ route('dashboard.saas-clients.create') }}" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 mt-2">
                        Cadastrar Primeiro Cliente
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

