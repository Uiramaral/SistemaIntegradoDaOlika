@extends('dashboard.layouts.app')

@section('page_title', 'Embalagens')
@section('page_subtitle', 'Gerenciamento de embalagens')

@section('page_actions')
    <a href="{{ route('dashboard.producao.embalagens.create') }}" class="btn-primary gap-2">
        <i data-lucide="plus" class="h-4 w-4"></i>
        Nova Embalagem
    </a>
@endsection

@section('content')
<div class="bg-card rounded-xl border border-border">
    <div class="p-4 border-b border-border flex flex-col sm:flex-row gap-4 justify-between">
        <form method="GET" action="{{ route('dashboard.producao.embalagens.index') }}" class="relative flex-1 max-w-md">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar embalagem..." class="form-input pl-10">
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-muted/50">
                <tr>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Nome</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Descrição</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Custo (R$)</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Status</th>
                    <th class="text-left text-xs font-semibold uppercase py-3 px-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packagings as $packaging)
                <tr class="border-b border-border hover:bg-muted/30">
                    <td class="py-3 px-4 font-medium">{{ $packaging->name }}</td>
                    <td class="py-3 px-4 text-sm text-muted-foreground">{{ $packaging->description ?? '-' }}</td>
                    <td class="py-3 px-4 font-semibold">R$ {{ number_format($packaging->cost, 2, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <span class="status-badge {{ $packaging->is_active ? 'status-badge-completed' : 'status-badge-pending' }}">
                            {{ $packaging->is_active ? 'Ativa' : 'Inativa' }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex gap-2">
                            <a href="{{ route('dashboard.producao.embalagens.edit', $packaging) }}" class="btn-outline h-8 w-8 p-0">
                                <i data-lucide="edit" class="h-4 w-4"></i>
                            </a>
                            <form action="{{ route('dashboard.producao.embalagens.destroy', $packaging) }}" method="POST" onsubmit="return confirm('Remover embalagem?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline text-destructive h-8 w-8 p-0">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center text-muted-foreground">
                        <i data-lucide="package" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
                        <p class="font-medium">Nenhuma embalagem cadastrada</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($packagings->hasPages())
    <div class="p-4 border-t border-border">
        {{ $packagings->links() }}
    </div>
    @endif
</div>
@endsection
