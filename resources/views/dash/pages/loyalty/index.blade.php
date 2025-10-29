@extends('dash.layouts.base')

@section('title', 'Fidelidade')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Pontos de Fidelidade</h1>
</div>

<div class="bg-white p-4 rounded-xl shadow">
    <table class="w-full text-left">
        <thead>
            <tr>
                <th class="py-2 border-b">Cliente</th>
                <th class="py-2 border-b">Pontos</th>
                <th class="py-2 border-b">Atualizado em</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loyalties as $loyalty)
            <tr>
                <td class="py-2 border-b">{{ $loyalty->customer->nome }}</td>
                <td class="py-2 border-b">{{ $loyalty->pontos }}</td>
                <td class="py-2 border-b">{{ $loyalty->updated_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection