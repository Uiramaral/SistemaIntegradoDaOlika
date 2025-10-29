@extends('dash.layouts.base')

@section('title', 'Produtos')

@section('sidebar')
    @include('dash.layouts.sidebar')
@endsection

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Lista de Produtos</h2>

    <table class="min-w-full text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left">ID</th>
                <th class="px-4 py-2 text-left">Nome</th>
                <th class="px-4 py-2 text-left">Categoria</th>
                <th class="px-4 py-2 text-left">Preço</th>
                <th class="px-4 py-2 text-left">Ativo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $produto)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $produto->id }}</td>
                    <td class="px-4 py-2">{{ $produto->nome }}</td>
                    <td class="px-4 py-2">{{ $produto->categoria->nome ?? '—' }}</td>
                    <td class="px-4 py-2">R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                    <td class="px-4 py-2">
                        @if($produto->ativo)
                            <span class="text-green-600 font-semibold">Sim</span>
                        @else
                            <span class="text-red-600 font-semibold">Não</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
