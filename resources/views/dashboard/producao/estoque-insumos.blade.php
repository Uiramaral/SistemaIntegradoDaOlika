@extends('dashboard.layouts.app')

@section('page_title', 'Estoque Insumos')
@section('page_subtitle', 'Controle de estoque de matéria-prima')

@section('content')
<div class="space-y-4 md:space-y-6">
    <div class="card-copycat overflow-hidden">
        <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                <input type="text" placeholder="Buscar insumo..." class="input-copycat pl-10 h-10">
            </div>
            <button type="button" class="btn-copycat-primary shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Nova Entrada
            </button>
        </div>
        <div class="overflow-x-auto scrollbar-thin">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Insumo</th>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Estoque</th>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Mínimo</th>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Status</th>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Última Entrada</th>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Fornecedor</th>
                        <th class="text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider py-3 px-4 bg-muted/50">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-muted-foreground">
                            <i data-lucide="boxes" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                            <p class="font-medium text-foreground">Nenhum insumo cadastrado</p>
                            <p class="text-sm mt-1">Registre insumos e entradas de matéria-prima.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
