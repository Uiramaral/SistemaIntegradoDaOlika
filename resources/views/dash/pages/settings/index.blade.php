@extends('dash.layouts.base')

@section('title', 'Configurações da Loja')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-xl font-bold mb-4">Configurações Gerais</h1>
    <form action="{{ route('dashboard.settings.update') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Nome da Loja</label>
                <input type="text" name="store_name" value="{{ old('store_name', $settings->store_name) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">E-mail</label>
                <input type="email" name="store_email" value="{{ old('store_email', $settings->store_email) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Telefone</label>
                <input type="text" name="store_phone" value="{{ old('store_phone', $settings->store_phone) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">CNPJ</label>
                <input type="text" name="store_cnpj" value="{{ old('store_cnpj', $settings->store_cnpj) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Taxa de Serviço (%)</label>
                <input type="number" name="service_fee" step="0.01" value="{{ old('service_fee', $settings->service_fee) }}" class="input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium">Pedido Mínimo (R$)</label>
                <input type="number" name="min_order" step="0.01" value="{{ old('min_order', $settings->min_order) }}" class="input w-full">
            </div>
        </div>
        <div class="text-right mt-6">
            <button class="btn btn-primary">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection