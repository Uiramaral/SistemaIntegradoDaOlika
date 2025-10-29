<!-- resources/views/dash/pages/cashback/edit.blade.php -->
@extends('layouts.dash')

@section('title', 'Editar Cashback')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow">
    <form action="{{ route('dashboard.cashback.update', $cashback->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-1">Cliente</label>
            <input type="text" name="customer_name" value="{{ $cashback->customer->name }}" class="w-full input" readonly />
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Valor</label>
            <input type="number" step="0.01" name="amount" value="{{ $cashback->amount }}" class="w-full input" required />
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Descrição</label>
            <textarea name="description" class="w-full input">{{ $cashback->description }}</textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection
