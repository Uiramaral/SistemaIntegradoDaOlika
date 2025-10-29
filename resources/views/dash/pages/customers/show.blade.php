{{-- resources/views/dash/pages/customers/show.blade.php --}}

@extends('dash.layouts.app')

@section('title', 'Cliente: ' . $customer->name)

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Cliente: {{ $customer->name }}</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Informações</h2>
        <ul class="text-sm text-gray-700">
            <li><strong>ID:</strong> {{ $customer->id }}</li>
            <li><strong>Nome:</strong> {{ $customer->name }}</li>
            <li><strong>Email:</strong> {{ $customer->email }}</li>
            <li><strong>Telefone:</strong> {{ $customer->phone }}</li>
            <li><strong>CPF:</strong> {{ $customer->cpf }}</li>
            <li><strong>Saldo Fiado:</strong> R$ {{ number_format($customer->balance, 2, ',', '.') }}</li>
            <li><strong>Último pedido:</strong> {{ optional($customer->last_order)->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</li>
        </ul>
    </div>

    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">Editar Cliente</h2>
        <form method="POST" action="{{ route('dashboard.customers.update', $customer) }}">
            @csrf
            <div class="mb-2">
                <label class="block text-sm">Nome</label>
                <input type="text" name="name" class="form-input w-full" value="{{ old('name', $customer->name) }}">
            </div>
            <div class="mb-2">
                <label class="block text-sm">Email</label>
                <input type="email" name="email" class="form-input w-full" value="{{ old('email', $customer->email) }}">
            </div>
            <div class="mb-2">
                <label class="block text-sm">Telefone</label>
                <input type="text" name="phone" class="form-input w-full" value="{{ old('phone', $customer->phone) }}">
            </div>
            <div class="mb-2">
                <label class="block text-sm">CPF</label>
                <input type="text" name="cpf" class="form-input w-full" value="{{ old('cpf', $customer->cpf) }}">
            </div>
            <div class="text-right">
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
