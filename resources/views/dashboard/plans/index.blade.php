@extends('dashboard.layouts.app')

@section('title', 'Módulos e Planos - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Módulos e Planos</h1>
            <p class="text-muted-foreground">Escolha o plano ideal para o seu negócio</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border bg-green-50 text-green-900 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border bg-red-50 text-red-900 px-4 py-3">
            {{ session('error') }}
        </div>
    @endif

    @if($currentClient)
        <!-- Plano Atual -->
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Plano Atual</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold">
                            @if($currentClient->plan === 'ia')
                                Plano WhatsApp
                            @else
                                Plano Básico
                            @endif
                        </p>
                        <p class="text-sm text-muted-foreground mt-1">
                            @if($currentClient->plan === 'ia')
                                Inclui todas as funcionalidades + WhatsApp
                            @else
                                Funcionalidades essenciais
                            @endif
                        </p>
                    </div>
                    @if($currentClient->active)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">Ativo</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">Inativo</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Planos Disponíveis -->
    <div class="grid gap-6 md:grid-cols-2">
        @foreach($plans as $planKey => $plan)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm {{ $currentClient && $currentClient->plan === $planKey ? 'ring-2 ring-primary' : '' }}">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold">{{ $plan['name'] }}</h3>
                        @if($currentClient && $currentClient->plan === $planKey)
                            <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">Plano Atual</span>
                        @endif
                    </div>
                    
                    <p class="text-muted-foreground mb-4">{{ $plan['description'] }}</p>
                    
                    <div class="mb-6">
                        <p class="text-3xl font-bold">{{ $plan['price'] }}</p>
                    </div>

                    <ul class="space-y-2 mb-6">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check text-green-600 mt-0.5 flex-shrink-0">
                                    <path d="M20 6 9 17l-5-5"></path>
                                </svg>
                                <span class="text-sm">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if($currentClient && $currentClient->plan !== $planKey)
                        <form action="{{ route('dashboard.plans.update') }}" method="POST" class="mt-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="plan" value="{{ $planKey }}">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4">
                                Escolher este Plano
                            </button>
                        </form>
                    @elseif(!$currentClient)
                        <div class="mt-4 p-3 bg-muted rounded-md">
                            <p class="text-sm text-muted-foreground mb-2">Você ainda não está vinculado a um cliente.</p>
                            <a href="{{ route('dashboard.settings') }}" class="text-sm text-primary hover:underline">Acesse as Configurações para mais informações</a>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Informações Adicionais -->
    <div class="rounded-lg border bg-muted/50 p-6">
        <h3 class="font-semibold mb-2">Informações Importantes</h3>
        <ul class="space-y-2 text-sm text-muted-foreground">
            <li>• A alteração de plano será aplicada no próximo ciclo de cobrança</li>
            <li>• Você pode alterar seu plano a qualquer momento</li>
            <li>• Não há taxa de cancelamento ou alteração</li>
            <li>• Todos os seus dados serão preservados ao alterar o plano</li>
        </ul>
    </div>
</div>
@endsection

