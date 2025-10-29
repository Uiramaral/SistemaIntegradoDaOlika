@extends('dash.layouts.base')

@section('title', 'Editar Produto')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Editar Produto</h1>
    <a href="{{ route('dashboard.products') }}" class="btn">Voltar</a>
</div>

<form action="{{ route('dashboard.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">Categoria</label>
            <select name="category_id" class="input w-full">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($category->id == $product->category_id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium">Preço (R$)</label>
            <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" class="input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">Status</label>
            <select name="active" class="input w-full">
                <option value="1" @selected($product->active)>Ativo</option>
                <option value="0" @selected(!$product->active)>Inativo</option>
            </select>
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium">Descrição</label>
        <textarea name="description" rows="4" class="input w-full">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="mt-6 text-right">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </div>
</form>
@endsection