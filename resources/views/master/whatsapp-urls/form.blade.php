@extends('layouts.admin')

@section('title', isset($instance) ? 'Editar Instância' : 'Nova Instância WhatsApp')
@section('page_title', isset($instance) ? 'Editar Instância' : 'Nova Instância WhatsApp')
@section('page_subtitle', 'Configure a URL da instância Railway')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="rounded-lg border border-border bg-card shadow-sm">
        <form action="{{ isset($instance) ? route('master.whatsapp-urls.update', $instance) : route('master.whatsapp-urls.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @if(isset($instance))
                @method('PUT')
            @endif

            <div>
                <label for="url" class="block text-sm font-medium text-foreground mb-1">URL da Instância *</label>
                <input type="url" name="url" id="url" value="{{ old('url', $instance->url ?? '') }}" 
                       {{ isset($instance) && $instance->client_id ? 'readonly' : 'required' }}
                       class="w-full px-3 py-2 rounded-md border border-border {{ isset($instance) && $instance->client_id ? 'bg-muted cursor-not-allowed' : 'bg-background' }} text-foreground focus:outline-none focus:ring-2 focus:ring-primary font-mono text-sm"
                       placeholder="https://olika-whatsapp-01.up.railway.app">
                <p class="text-xs text-muted-foreground mt-1">URL completa da instância no Railway. O nome será extraído automaticamente.</p>
                @error('url')
                    <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if(isset($instance))
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Nome da Instância</label>
                    <input type="text" value="{{ $instance->name }}" readonly
                           class="w-full px-3 py-2 rounded-md border border-border bg-muted text-muted-foreground cursor-not-allowed font-mono text-sm">
                    <p class="text-xs text-muted-foreground mt-1">Extraído automaticamente da URL</p>
                </div>
            @endif

            @if(isset($instance) && $instance->client_id)
                <div class="p-4 rounded-lg border border-primary/30 bg-primary/5">
                    <p class="text-sm text-foreground">
                        <strong>Atribuída a:</strong> {{ $instance->client->name ?? 'Cliente ID: ' . $instance->client_id }}
                    </p>
                </div>
            @endif

            <div class="flex items-center gap-3 pt-4 border-t border-border">
                <button type="submit" class="px-6 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition font-medium">
                    {{ isset($instance) ? 'Atualizar' : 'Criar Instância' }}
                </button>
                <a href="{{ route('master.whatsapp-urls.index') }}" class="px-6 py-2 bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
