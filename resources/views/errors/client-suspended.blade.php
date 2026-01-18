@extends('layouts.guest')

@section('title', 'Estabelecimento indisponível')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
            Estabelecimento temporariamente indisponível
        </h1>
        
        @if(isset($client))
        <p class="text-lg font-medium text-gray-700 mb-4">
            {{ $client->name }}
        </p>
        @endif
        
        <p class="text-gray-600 mb-6">
            Este estabelecimento está temporariamente fora do ar. 
            Isso pode acontecer por diversos motivos, como manutenção programada ou questões administrativas.
        </p>
        
        <div class="bg-amber-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-amber-800">
                <strong>O que você pode fazer:</strong>
            </p>
            <ul class="text-sm text-amber-700 mt-2 space-y-1 text-left">
                <li>• Tente novamente mais tarde</li>
                <li>• Entre em contato diretamente com o estabelecimento</li>
                <li>• Verifique as redes sociais do estabelecimento para atualizações</li>
            </ul>
        </div>
        
        <a href="javascript:history.back()" 
           class="inline-block px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
            Voltar
        </a>
        
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400">
                Se você é o proprietário deste estabelecimento,
                <a href="mailto:suporte@menuonline.com.br" class="text-primary hover:underline">entre em contato com o suporte</a>.
            </p>
        </div>
    </div>
</div>
@endsection
