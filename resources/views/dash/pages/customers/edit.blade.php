@extends('dash.layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Editar Cliente</h1>
    <a href="{{ route('dashboard.customers') }}" class="btn">Voltar</a>
</div>

@if(session('success'))
    <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('dashboard.customers.update', $customer->id) }}" method="POST" class="bg-white p-6 rounded shadow">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Nome</label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">E-mail</label>
            <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">Telefone</label>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium">CPF</label>
            <input type="text" name="cpf" value="{{ old('cpf', $customer->cpf) }}" class="input w-full">
        </div>
    </div>
    <div class="mt-6 text-right">
        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </div>
</form>
@endsection
