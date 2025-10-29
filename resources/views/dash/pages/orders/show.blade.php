@extends('dash.layouts.base')

@section('title', 'Detalhes do Pedido')

@section('sidebar')
    @include('dash.layouts.sidebar')
@endsection

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Pedido #OLK{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</h2>

    <div class="grid grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-2">Cliente</h3>
            <p><strong>Nome:</strong> {{ $pedido->cliente->nome ?? '—' }}</p>
            <p><strong>Email:</strong> {{ $pedido->cliente->email ?? '—' }}</p>
            <p><strong>Telefone:</strong> {{ $pedido->cliente->telefone ?? '—' }}</p>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-2">Pedido</h3>
            <p><strong>Status:</strong> {{ $pedido->status }}</p>
            <p><strong>Total:</strong> R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
            <p><strong>Data:</strong> {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-2">Atualizar Status</h3>
        <form action="{{ route('dashboard.orders.status', $pedido->id) }}" method="POST" class="flex items-center gap-4">
            @csrf
            <select name="status_code" class="border px-2 py-1 rounded">
                <option value="pending" @selected($pedido->status == 'pending')>Pendente</option>
                <option value="confirmed" @selected($pedido->status == 'confirmed')>Confirmado</option>
                <option value="preparing" @selected($pedido->status == 'preparing')>Preparando</option>
                <option value="delivered" @selected($pedido->status == 'delivered')>Pronto</option>
            </select>
            <button class="bg-orange-500 text-white px-4 py-1 rounded">Atualizar</button>
        </form>
    </div>
</div>
@endsection
