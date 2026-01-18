@extends('layouts.guest')

@section('title', 'Estabelecimento não encontrado')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
            Estabelecimento não encontrado
        </h1>
        
        <p class="text-gray-600 mb-6">
            O endereço que você está tentando acessar não corresponde a nenhum estabelecimento cadastrado em nossa plataforma.
        </p>
        
        <div class="space-y-3">
            <p class="text-sm text-gray-500">
                Verifique se o endereço digitado está correto ou entre em contato com o estabelecimento para obter o link correto.
            </p>
            
            <a href="https://menuonline.com.br" 
               class="inline-block px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-600 transition-colors">
                Ir para o site principal
            </a>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400">
                Se você é proprietário de um estabelecimento e gostaria de usar nossa plataforma,
                <a href="https://menuonline.com.br/contato" class="text-primary hover:underline">entre em contato</a>.
            </p>
        </div>
    </div>
</div>
@endsection
