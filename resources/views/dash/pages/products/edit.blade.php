@extends('layouts.admin')

@section('title', 'Editar Produto')
@section('page_title', 'Editar Produto')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Editar Produto</h1>
    <a href="{{ route('dashboard.products.index') }}" class="btn btn-secondary">Voltar</a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<form action="{{ route('dashboard.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-2">Nome</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="input w-full" required>
            @error('name')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Categoria</label>
            <select name="category_id" class="input w-full">
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category->id }}" @selected($category->id == $product->category_id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Preço (R$)</label>
            <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" class="input w-full" required>
            @error('price')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <select name="active" class="input w-full">
                <option value="1" @selected($product->active)>Ativo</option>
                <option value="0" @selected(!$product->active)>Inativo</option>
            </select>
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium mb-2">Descrição</label>
        <textarea name="description" rows="4" class="input w-full">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="mt-6 text-right">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </div>
</form>
@endsection