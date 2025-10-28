@extends('layout.app')

@section('content')
    <div class="page">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 bg-white rounded shadow text-center">
                <div class="text-gray-500">Total de Pedidos</div>
                <div class="text-xl font-bold">0</div>
            </div>
            <div class="p-4 bg-white rounded shadow text-center">
                <div class="text-gray-500">Faturamento</div>
                <div class="text-xl font-bold">R$ 0,00</div>
            </div>
            <div class="p-4 bg-white rounded shadow text-center">
                <div class="text-gray-500">Novos Clientes</div>
                <div class="text-xl font-bold">0</div>
            </div>
            <div class="p-4 bg-white rounded shadow text-center">
                <div class="text-gray-500">Ticket Médio</div>
                <div class="text-xl font-bold">R$ 0,00</div>
            </div>
        </div>
    </div>
@endsection
