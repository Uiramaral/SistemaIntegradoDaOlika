@extends('dash.layouts.base')

@section('title', 'Ponto de Venda (PDV)')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="mb-4">
            <label class="block text-sm font-medium">Buscar Produto (Enter)</label>
            <input type="text" name="search" id="product-search" class="input w-full" placeholder="Digite o nome ou código do produto">
        </div>
        <div class="bg-white rounded-xl p-4 shadow h-[500px] overflow-y-auto">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Qtd</th>
                        <th>Preço</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="cart-items">
                    <!-- Itens dinâmicos -->
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="bg-white p-4 rounded-xl shadow mb-4">
            <label class="block text-sm font-medium mb-1">Cliente</label>
            <select name="customer_id" class="input w-full">
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="bg-white p-4 rounded-xl shadow text-right">
            <h2 class="text-sm text-gray-600">Total</h2>
            <div class="text-3xl font-bold mb-4" id="cart-total">R$ 0,00</div>
            <button class="btn btn-primary w-full" id="finalize-sale">Finalizar Venda (F2)</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.getElementById('cart-items').innerHTML = '';
            document.getElementById('cart-total').textContent = 'R$ 0,00';
        }
    });
    // Lógica adicional de busca/adicionar produtos e calcular totais pode ser implementada com JS ou Vue.js
</script>
@endpush
@endsection