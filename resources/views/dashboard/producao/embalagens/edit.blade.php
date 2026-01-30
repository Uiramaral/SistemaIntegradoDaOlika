@extends('dashboard.layouts.app')

@section('page_title', 'Editar Embalagem')

@section('content')
<div class="space-y-6">
    <form action="{{ route('dashboard.producao.embalagens.update', $packaging) }}" method="POST" class="bg-card rounded-xl border border-border p-6 space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-2">Nome *</label>
                <input type="text" name="name" value="{{ old('name', $packaging->name) }}" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Custo (R$) *</label>
                <input type="number" name="cost" value="{{ old('cost', $packaging->cost) }}" step="0.01" min="0" required class="form-input">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">Descrição</label>
            <textarea name="description" rows="3" class="form-input">{{ old('description', $packaging->description) }}</textarea>
        </div>
        
        <div class="flex gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $packaging->is_active) ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Ativa</span>
            </label>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" class="btn-primary">Salvar</button>
            <a href="{{ route('dashboard.producao.embalagens.index') }}" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
