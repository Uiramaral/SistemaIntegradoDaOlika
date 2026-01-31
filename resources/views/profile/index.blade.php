@extends('layouts.dashboard')

@section('title', 'Meu Perfil')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Card Dados Pessoais --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Dados Pessoais</h2>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Seu Nome</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="mt-6">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Salvar Dados Pessoais</button>
                </div>
            </form>
        </div>

        {{-- Card Dados do Estabelecimento --}}
        @if($user->client)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Dados do Estabelecimento</h2>

                {{-- Form de Nome da Loja --}}
                <form action="{{ route('profile.update') }}" method="POST" class="mb-6 border-b pb-6">
                    @csrf
                    @method('PATCH')
                     <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nome da Loja</label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name', $user->client->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Este é o nome que aparecerá no cabeçalho do seu cardápio.</p>
                    </div>
                     <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">Atualizar Nome da Loja</button>
                </form>

                {{-- Form de URL --}}
                <form action="{{ route('cozinha.profile.slug.update') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Endereço Web (URL)</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $user->client->slug) }}" class="flex-1 rounded-none rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="minha-loja">
                            <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">.cozinhapro.app.br</span>
                        </div>
                        <div id="slug-feedback" class="text-sm mt-1"></div>
                    </div>

                    @if($user->client->slug)
                        <div class="mb-4">
                            <a href="https://{{ $user->client->slug }}.cozinhapro.app.br" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                Acessar minha loja
                            </a>
                        </div>
                    @endif

                    <div class="mt-6">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" id="btn-save-slug">Atualizar URL</button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Seu usuário não está vinculado a nenhum estabelecimento (Store/Client). Entre em contato com o suporte.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Script de geração de slug --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const storeNameInput = document.getElementById('store_name');
        const slugInput = document.getElementById('slug');
        const feedbackDiv = document.getElementById('slug-feedback');
        const saveBtn = document.getElementById('btn-save-slug');
        let typingTimer;

        if (storeNameInput && slugInput) {
            // Gera slug ao digitar o nome da loja (apenas se slug estiver vazio)
            storeNameInput.addEventListener('input', function() {
                if (slugInput.value.trim() === '') {
                    generateSlug(this.value);
                }
            });

            // Formata slug enquanto digita no campo slug
            slugInput.addEventListener('input', function() {
                 let val = this.value.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, "")
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');

                 if (this.value !== val) {
                     this.value = val;
                 }

                 checkAvailability(val);
            });

            function generateSlug(text) {
                 let slug = text.toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, "")
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');

                 slugInput.value = slug;
                 checkAvailability(slug);
            }

            function checkAvailability(slug) {
                clearTimeout(typingTimer);
                if (!slug || slug.length < 3) {
                    feedbackDiv.innerHTML = '';
                    return;
                }

                feedbackDiv.innerHTML = '<span class="text-gray-500">Verificando...</span>';
                saveBtn.disabled = true;

                typingTimer = setTimeout(() => {
                    fetch('{{ route("profile.check.slug") }}?slug=' + slug, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.available) {
                            feedbackDiv.innerHTML = `<span class="text-green-600">✓ ${data.message} (${data.url})</span>`;
                            saveBtn.disabled = false;
                            storeNameInput.classList.remove('border-red-500'); // Remove erro visual se estava
                        } else {
                            feedbackDiv.innerHTML = `<span class="text-red-600">✗ ${data.message}</span>`;
                            saveBtn.disabled = true;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        feedbackDiv.innerHTML = '<span class="text-red-500">Erro ao verificar.</span>';
                    });
                }, 500);
            }
        }
    });
    </script>
@endsection
