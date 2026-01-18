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
@endsection
