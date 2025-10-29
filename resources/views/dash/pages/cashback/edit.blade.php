@extends('layouts.admin')

@section('title', 'Editar Cashback')
@section('page_title', 'Editar Cashback')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Editar Cashback</h1>
        <a href="{{ route('dashboard.cashback.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <form action="{{ route('dashboard.cashback.update', $cashback->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-2">Cliente</label>
            <input type="text" name="customer_name" value="{{ $cashback->customer->nome ?? 'Cliente não encontrado' }}" class="w-full input" readonly />
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Valor</label>
            <input type="number" step="0.01" name="valor" value="{{ $cashback->valor ?? 0 }}" class="w-full input" required />
            @error('valor')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Status</label>
            <select name="status" class="w-full input">
                <option value="pending" @selected($cashback->status == 'pending')>Pendente</option>
                <option value="approved" @selected($cashback->status == 'approved')>Aprovado</option>
                <option value="rejected" @selected($cashback->status == 'rejected')>Rejeitado</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-2">Descrição</label>
            <textarea name="description" class="w-full input">{{ $cashback->description ?? '' }}</textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection
