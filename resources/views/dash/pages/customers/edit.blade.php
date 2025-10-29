@extends('layouts.admin')

@section('title', 'Editar Cliente')
@section('page_title', 'Editar Cliente')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Editar Cliente</h1>
    <a href="{{ route('dashboard.customers.index') }}" class="btn btn-secondary">Voltar</a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<form action="{{ route('dashboard.customers.update', $customer->id) }}" method="POST" class="bg-white p-6 rounded-xl shadow">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-2">Nome</label>
            <input type="text" name="nome" value="{{ old('nome', $customer->nome) }}" class="input w-full" required>
            @error('nome')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">E-mail</label>
            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="input w-full">
            @error('email')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Telefone</label>
            <input type="text" name="telefone" value="{{ old('telefone', $customer->telefone) }}" class="input w-full">
            @error('telefone')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">CPF</label>
            <input type="text" name="cpf" value="{{ old('cpf', $customer->cpf) }}" class="input w-full">
            @error('cpf')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="mt-6 text-right">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </div>
</form>
@endsection
