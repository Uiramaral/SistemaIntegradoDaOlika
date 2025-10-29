@extends('dash.layouts.app')

@section('title', 'PDV - OLIKA Dashboard')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">PDV</h1>
        <p class="text-muted-foreground">Ponto de Venda - Em desenvolvimento</p>
    </div>
    
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6">
            <h3 class="text-2xl font-semibold leading-none tracking-tight">Ponto de Venda</h3>
        </div>
        <div class="p-6 pt-0">
            <div class="flex flex-col items-center justify-center py-12 text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart h-16 w-16 mb-4 opacity-20">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
                <p class="text-lg">Módulo PDV em desenvolvimento</p>
            </div>
        </div>
    </div>
</div>
@endsection