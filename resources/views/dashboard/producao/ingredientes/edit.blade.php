@extends('dashboard.layouts.app')

@section('page_title', 'Editar Ingrediente')

@section('content')
<div class="space-y-6">
    <form action="{{ route('dashboard.producao.ingredientes.update', $ingredient) }}" method="POST" class="bg-card rounded-xl border border-border p-6 space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium mb-2">Nome *</label>
                <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" required class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Categoria</label>
                <input type="text" name="category" value="{{ old('category', $ingredient->category) }}" list="categories-list" class="form-input">
                <datalist id="categories-list">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Unidade</label>
                <select name="unit" class="form-input">
                    <option value="g" {{ old('unit', $ingredient->unit) == 'g' ? 'selected' : '' }}>g</option>
                    <option value="kg" {{ old('unit', $ingredient->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                    <option value="ml" {{ old('unit', $ingredient->unit) == 'ml' ? 'selected' : '' }}>ml</option>
                    <option value="l" {{ old('unit', $ingredient->unit) == 'l' ? 'selected' : '' }}>l</option>
                    <option value="un" {{ old('unit', $ingredient->unit) == 'un' ? 'selected' : '' }}>un</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Peso da Embalagem</label>
                <input type="number" name="package_weight" value="{{ old('package_weight', $ingredient->package_weight) }}" step="0.01" min="0" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Custo (R$)</label>
                <input type="number" name="cost" value="{{ old('cost', $ingredient->cost) }}" step="0.01" min="0" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Estoque Atual</label>
                <input type="number" name="stock" value="{{ old('stock', $ingredient->stock) }}" step="0.01" min="0" class="form-input">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Estoque Mínimo</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', $ingredient->min_stock) }}" step="0.01" min="0" class="form-input">
            </div>
        </div>
        
        <div class="flex gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_flour" value="1" {{ old('is_flour', $ingredient->is_flour) ? 'checked' : '' }} class="rounded">
                <span class="text-sm">É farinha</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $ingredient->is_active) ? 'checked' : '' }} class="rounded">
                <span class="text-sm">Ativo</span>
            </label>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" class="btn-primary">Atualizar</button>
            <a href="{{ route('dashboard.producao.ingredientes.index') }}" class="btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection
