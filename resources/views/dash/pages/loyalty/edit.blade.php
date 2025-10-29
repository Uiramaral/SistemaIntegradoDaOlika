<!-- resources/views/dash/pages/loyalty/edit.blade.php -->
@extends('layouts.dash')

@section('title', 'Editar Fidelidade')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow">
    <form action="{{ route('dashboard.loyalty.update', $loyalty->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-semibold mb-1">Cliente</label>
            <input type="text" name="customer_name" value="{{ $loyalty->customer->name }}" class="w-full input" readonly />
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Pontos</label>
            <input type="number" name="points" value="{{ $loyalty->points }}" class="w-full input" required />
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Descrição</label>
            <textarea name="description" class="w-full input">{{ $loyalty->description }}</textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>
@endsection
