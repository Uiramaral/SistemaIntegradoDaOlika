@extends('dash.layouts.base')

@section('title', 'Editar Categoria')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <form action="{{ route('dashboard.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block text-sm font-medium">Nome</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="input w-full">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium">Status</label>
            <select name="active" class="input w-full">
                <option value="1" @selected($category->active)>Ativa</option>
                <option value="0" @selected(!$category->active)>Inativa</option>
            </select>
        </div>
        
        <div class="text-right">
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection
