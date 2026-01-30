@extends('dashboard.layouts.app')

@section('page_title', 'Guia Técnico Completo')
@section('page_subtitle', 'Referência para replicar o layout e comportamento da aplicação')

@section('content')
<div class="space-y-6">
    {{-- Ações --}}
    <div class="flex flex-wrap gap-3">
        <a href="https://ui-copycat-helper.lovable.app/guia-tecnico-completo" target="_blank" rel="noopener"
           class="btn-copycat-primary inline-flex items-center gap-2">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            <span class="hidden sm:inline">Abrir guia (referência)</span>
            <span class="sm:hidden">Abrir guia</span>
        </a>
    </div>

    {{-- Cards informativos --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
        <div class="card-copycat p-3 md:p-4 flex items-center gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-4 h-4 md:w-5 md:h-5 text-primary"></i>
            </div>
            <div class="min-w-0">
                <p class="text-lg md:text-2xl font-bold text-foreground">23</p>
                <p class="text-[10px] md:text-xs text-muted-foreground truncate">Seções</p>
            </div>
        </div>
        <div class="card-copycat p-3 md:p-4 flex items-center gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-accent/10 flex items-center justify-center shrink-0">
                <i data-lucide="layers" class="w-4 h-4 md:w-5 md:h-5 text-accent"></i>
            </div>
            <div class="min-w-0">
                <p class="text-lg md:text-2xl font-bold text-foreground">20+</p>
                <p class="text-[10px] md:text-xs text-muted-foreground truncate">Componentes</p>
            </div>
        </div>
        <div class="card-copycat p-3 md:p-4 flex items-center gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                <i data-lucide="palette" class="w-4 h-4 md:w-5 md:h-5 text-amber-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-lg md:text-2xl font-bold text-foreground">9</p>
                <p class="text-[10px] md:text-xs text-muted-foreground truncate">Cores</p>
            </div>
        </div>
        <div class="card-copycat p-3 md:p-4 flex items-center gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                <i data-lucide="smartphone" class="w-4 h-4 md:w-5 md:h-5 text-blue-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-lg md:text-2xl font-bold text-foreground">PWA</p>
                <p class="text-[10px] md:text-xs text-muted-foreground truncate">Instalável</p>
            </div>
        </div>
        <div class="card-copycat p-3 md:p-4 flex items-center gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                <i data-lucide="bot" class="w-4 h-4 md:w-5 md:h-5 text-primary"></i>
            </div>
            <div class="min-w-0">
                <p class="text-lg md:text-2xl font-bold text-foreground">IA</p>
                <p class="text-[10px] md:text-xs text-muted-foreground truncate">Assistente</p>
            </div>
        </div>
        <div class="card-copycat p-3 md:p-4 flex items-center gap-3">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                <i data-lucide="zap" class="w-4 h-4 md:w-5 md:h-5 text-primary"></i>
            </div>
            <div class="min-w-0">
                <p class="text-lg md:text-2xl font-bold text-foreground">v3.0</p>
                <p class="text-[10px] md:text-xs text-muted-foreground truncate">Versão</p>
            </div>
        </div>
    </div>

    {{-- Conteúdo do guia (resumo) --}}
    <div class="card-copycat p-4 md:p-6">
        <h2 class="text-lg md:text-xl font-bold text-foreground mb-4">Sobre o Guia v3.0</h2>
        <p class="text-muted-foreground mb-4 text-sm md:text-base">
            Este guia contém as instruções para replicar o layout e comportamento do sistema (Confeitaria Pro / ui-copycat-helper) em Laravel/PHP.
            Referência: <code class="text-xs bg-muted px-1.5 py-0.5 rounded">temp/ui-copycat-helper</code>.
        </p>
        <ul class="space-y-2 text-muted-foreground text-sm mb-6">
            <li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-0.5"></i> Estrutura de diretórios Laravel</li>
            <li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-0.5"></i> Tailwind CSS, variáveis, design tokens</li>
            <li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-0.5"></i> Layouts (auth, app), Sidebar, Header</li>
            <li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-0.5"></i> Login, registro, onboarding</li>
            <li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-0.5"></i> PWA (manifest, SW, banner instalação)</li>
            <li class="flex items-start gap-2"><i data-lucide="check" class="w-4 h-4 text-accent shrink-0 mt-0.5"></i> Responsividade (mobile, tablet)</li>
        </ul>
        <div class="border border-border rounded-lg p-4 bg-muted/30 overflow-x-auto scrollbar-thin">
            <pre class="text-xs md:text-sm font-mono whitespace-pre-wrap text-foreground">Guia completo: temp/ui-copycat-helper → src/pages/GuiaTecnicoCompleto.tsx (guideContent).
Referência online: https://ui-copycat-helper.lovable.app/guia-tecnico-completo</pre>
        </div>
    </div>
</div>
@endsection
