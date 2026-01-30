@extends('dashboard.layouts.app')

@section('page_title', 'Novo Ingrediente')

@section('content')
<div class="space-y-6">
    <form action="{{ route('dashboard.producao.ingredientes.store') }}" method="POST" class="bg-card rounded-xl border border-border p-6 space-y-6">
        @csrf
        
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-2">Nome *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Categoria</label>
                <input type="text" name="category" value="{{ old('category', 'outro') }}" list="categories-list" class="form-input">
                <datalist id="categories-list">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Unidade</label>
                <select name="unit" class="form-input">
                    <option value="g" {{ old('unit', 'g') == 'g' ? 'selected' : '' }}>g (gramas)</option>
                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg (quilogramas)</option>
                    <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ml (mililitros)</option>
                    <option value="l" {{ old('unit') == 'l' ? 'selected' : '' }}>l (litros)</option>
                    <option value="un" {{ old('unit') == 'un' ? 'selected' : '' }}>un (unidade)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Peso da Embalagem</label>
                <input type="number" name="package_weight" value="{{ old('package_weight') }}" step="0.01" min="0" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Custo (R$)</label>
                <input type="number" name="cost" value="{{ old('cost', 0) }}" step="0.01" min="0" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Estoque Atual</label>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" step="0.01" min="0" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Estoque Mínimo</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" step="0.01" min="0" class="form-input">
            </div>
        </div>
        
        <div class="flex gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_flour" value="1" {{ old('is_flour') ? 'checked' : '' }} class="rounded">
                <span class="text-sm">É farinha</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Ativo</span>
            </label>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" class="btn-primary">Salvar</button>
            <a href="{{ route('dashboard.producao.ingredientes.index') }}" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
