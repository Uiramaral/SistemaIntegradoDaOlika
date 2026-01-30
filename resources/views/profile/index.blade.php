@extends('layouts.dashboard')

@section('title', 'Meu Perfil')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Configurações do Perfil</h6>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {!! session('success') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Informações Gerais -->
                    <form method="POST" action="{{ route('cozinha.profile.update') }}" class="mb-4">
                        @csrf
                        <h6 class="mb-3">Informações Gerais</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nome do Estabelecimento</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Salvar Informações</button>
                    </form>

                    <hr class="my-4">

                    <!-- URL Personalizada -->
                    <form method="POST" action="{{ route('cozinha.profile.slug.update') }}" id="slugForm">
                        @csrf
                        <h6 class="mb-3">URL Personalizada da Sua Loja</h6>
                        <p class="text-sm text-muted">
                            Esta é a URL que seus clientes usarão para acessar seu cardápio online.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="slug" class="form-label">Nome da URL</label>
                                <div class="input-group">
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="slug" 
                                        name="slug" 
                                        value="{{ old('slug', $user->slug) }}" 
                                        pattern="[a-z0-9\-]+"
                                        minlength="3"
                                        maxlength="30"
                                        placeholder="meu-estabelecimento"
                                        required
                                    >
                                    <span class="input-group-text">.cozinhapro.app.br</span>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Use apenas letras minúsculas, números e hifens. Mínimo 3 caracteres.
                                </small>
                                <div id="slugFeedback" class="mt-2"></div>
                            </div>
                            
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-info me-2" id="checkSlugBtn">
                                    <i class="fas fa-check-circle"></i> Verificar Disponibilidade
                                </button>
                            </div>
                        </div>
                        
                        @if($user->slug)
                        <div class="alert alert-info">
                            <strong>Sua URL atual:</strong> 
                            <a href="https://{{ $user->slug }}.cozinhapro.app.br" target="_blank" class="alert-link">
                                {{ $user->slug }}.cozinhapro.app.br
                            </a>
                        </div>
                        @endif
                        
                        <div class="alert alert-warning">
                            <strong>⚠️ Atenção:</strong> Ao mudar sua URL, links antigos enviados no WhatsApp de clientes não funcionarão mais.
                        </div>
                        
                        <button type="submit" class="btn btn-success" id="saveSlugBtn">
                            <i class="fas fa-save"></i> Salvar URL
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slugInput = document.getElementById('slug');
    const checkSlugBtn = document.getElementById('checkSlugBtn');
    const slugFeedback = document.getElementById('slugFeedback');
    const saveSlugBtn = document.getElementById('saveSlugBtn');
    
    // Forçar minúsculas e remover caracteres inválidos
    slugInput.addEventListener('input', function() {
        let value = this.value.toLowerCase();
        value = value.replace(/[^a-z0-9\-]/g, '');
        this.value = value;
        slugFeedback.innerHTML = '';
    });
    
    // Verificar disponibilidade
    checkSlugBtn.addEventListener('click', function() {
        const slug = slugInput.value.trim();
        
        if (!slug || slug.length < 3) {
            slugFeedback.innerHTML = '<div class="alert alert-warning">Digite pelo menos 3 caracteres.</div>';
            return;
        }
        
        checkSlugBtn.disabled = true;
        checkSlugBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        
        fetch('{{ route("cozinha.profile.slug.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ slug: slug })
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                slugFeedback.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> ${data.message}
                        <br><strong>Sua URL será:</strong> <code>${data.url}</code>
                    </div>
                `;
            } else {
                slugFeedback.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            slugFeedback.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Erro ao verificar disponibilidade.
                </div>
            `;
        })
        .finally(() => {
            checkSlugBtn.disabled = false;
            checkSlugBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verificar Disponibilidade';
        });
    });
});
</script>
@endpush
@endsection
