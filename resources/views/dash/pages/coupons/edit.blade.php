@extends('dash.layouts.base')

@section('title', 'Editar Cupom')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow">
    <form action="{{ route('dashboard.coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block text-sm font-medium">Código</label>
            <input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="input w-full">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium">Desconto (%)</label>
            <input type="number" name="discount" value="{{ old('discount', $coupon->discount) }}" class="input w-full">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium">Status</label>
            <select name="active" class="input w-full">
                <option value="1" @selected($coupon->active)>Ativo</option>
                <option value="0" @selected(!$coupon->active)>Inativo</option>
            </select>
        </div>
        
        <div class="text-right">
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection
