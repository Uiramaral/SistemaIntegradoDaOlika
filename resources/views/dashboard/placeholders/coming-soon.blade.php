@extends('dashboard.layouts.app')

@section('page_title', $title ?? 'Em Breve')
@section('page_subtitle', 'Este módulo está em desenvolvimento e estará disponível em breve.')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-lg border border-border shadow-sm p-12 text-center">
    <div class="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-6">
        <i data-lucide="construction" class="w-10 h-10"></i>
    </div>
    <h2 class="text-2xl font-bold text-foreground mb-2">Em Desenvolvimento</h2>
    <p class="text-muted-foreground max-w-md mx-auto mb-8">
        Estamos trabalhando para trazer as melhores ferramentas de gestão de produção para você. Fique atento às nossas atualizações!
    </p>
    <a href="{{ route('dashboard.index') }}" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-primary/90">
        Voltar ao Dashboard
    </a>
</div>
@endsection
