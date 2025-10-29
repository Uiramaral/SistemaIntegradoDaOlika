@extends('dash.layouts.base')

@section('title', 'Cashback')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Histórico de Cashback</h1>
</div>

<div class="bg-white p-4 rounded-xl shadow">
    <table class="w-full text-left">
        <thead>
            <tr>
                <th class="py-2 border-b">Cliente</th>
                <th class="py-2 border-b">Valor</th>
                <th class="py-2 border-b">Data</th>
                <th class="py-2 border-b">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashbacks as $cashback)
            <tr>
                <td class="py-2 border-b">{{ $cashback->customer->nome }}</td>
                <td class="py-2 border-b">R$ {{ number_format($cashback->valor, 2, ',', '.') }}</td>
                <td class="py-2 border-b">{{ $cashback->created_at->format('d/m/Y') }}</td>
                <td class="py-2 border-b">{{ $cashback->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection