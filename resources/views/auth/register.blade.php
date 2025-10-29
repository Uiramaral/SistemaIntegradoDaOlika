@extends('layouts.auth')

@section('title', 'Registro - Olika Admin')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="text-4xl mb-4">🍞</div>
            <h1 class="text-2xl font-bold text-orange-600 mb-2">Olika Admin</h1>
            <p class="text-gray-600">Criar nova conta de administrador</p>
        </div>

        <!-- Mensagens de Feedback -->
        @if(session('error'))
            <div class="alert alert-error">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span class="font-medium">Erros encontrados:</span>
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário de Registro -->
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="name">
                    <i class="fas fa-user mr-2"></i>Nome Completo
                </label>
                <input 
                    id="name" 
                    name="name" 
                    type="text" 
                    required
                    value="{{ old('name') }}"
                    class="input"
                    placeholder="Digite seu nome completo"
                >
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                    <i class="fas fa-envelope mr-2"></i>E-mail
                </label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    required
                    value="{{ old('email') }}"
                    class="input"
                    placeholder="Digite seu e-mail"
                >
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="password">
                    <i class="fas fa-lock mr-2"></i>Senha
                </label>
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    required
                    class="input"
                    placeholder="Mínimo 6 caracteres"
                >
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    A senha deve ter pelo menos 6 caracteres
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="password_confirmation">
                    <i class="fas fa-lock mr-2"></i>Confirmar Senha
                </label>
                <input 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    required
                    class="input"
                    placeholder="Digite a senha novamente"
                >
            </div>

            <button type="submit" class="btn btn-primary w-full py-3 text-lg mb-4">
                <i class="fas fa-user-plus mr-2"></i>
                Criar Conta
            </button>
        </form>

        <!-- Links Adicionais -->
        <div class="text-center">
            <p class="text-gray-600 text-sm">
                Já tem uma conta? 
                <a href="{{ route('login') }}" class="text-orange-600 hover:text-orange-700 font-medium">
                    Fazer Login
                </a>
            </p>
        </div>

        <!-- Informações do Sistema -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="text-center text-xs text-gray-500">
                <p>Sistema Olika Admin v1.0</p>
                <p>© {{ date('Y') }} Todos os direitos reservados</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-focus no campo de nome
    document.getElementById('name').focus();
    
    // Validação de senha em tempo real
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const confirmPassword = document.getElementById('password_confirmation');
        
        if (password.length < 6) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
        
        // Verificar se as senhas coincidem
        if (confirmPassword.value && password !== confirmPassword.value) {
            confirmPassword.style.borderColor = '#ef4444';
        } else if (confirmPassword.value) {
            confirmPassword.style.borderColor = '#10b981';
        }
    });
    
    document.getElementById('password_confirmation').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
    });
    
    // Limpar mensagens após 5 segundos
    setTimeout(function() {
        const messages = document.querySelectorAll('.alert');
        messages.forEach(msg => {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 300);
        });
    }, 5000);
</script>
@endpush
@endsection