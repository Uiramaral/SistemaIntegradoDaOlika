@extends('dash.layouts.base')

@section('title', 'Cupons')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Cupons</h1>
    <a href="{{ route('dashboard.coupons.create') }}" class="btn btn-primary">Novo Cupom</a>
</div>

<div class="bg-white rounded-xl p-4 shadow">
    <table class="w-full text-left">
        <thead>
            <tr>
                <th class="py-2 border-b">Código</th>
                <th class="py-2 border-b">Desconto</th>
                <th class="py-2 border-b">Status</th>
                <th class="py-2 border-b text-right">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coupons as $coupon)
                <tr>
                    <td class="py-2 border-b">{{ $coupon->code }}</td>
                    <td class="py-2 border-b">{{ $coupon->discount }}%</td>
                    <td class="py-2 border-b">{{ $coupon->active ? 'Ativo' : 'Inativo' }}</td>
                    <td class="py-2 border-b text-right">
                        <a href="{{ route('dashboard.coupons.edit', $coupon->id) }}" class="text-orange-600 hover:underline">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection