<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Menu Olika</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            margin-bottom: 1rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="login-container">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-orange-600 rounded-lg mx-auto mb-4 flex items-center justify-center">
                <i class="fa fa-lock text-white text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Olika Dashboard</h2>
            <p class="text-gray-600">Faça login para acessar o painel</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-envelope mr-2"></i>Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    placeholder="Digite seu email"
                    value="{{ old('email') }}"
                >
            </div>
            
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa fa-lock mr-2"></i>Senha
                </label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                    placeholder="Digite sua senha"
                >
            </div>
            
            <button 
                type="submit" 
                class="w-full bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition duration-200"
            >
                <i class="fa fa-sign-in-alt mr-2"></i>Entrar
            </button>
        </form>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mt-4">
                <i class="fa fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 text-center text-sm text-gray-500">
            <p><strong>Credenciais de teste:</strong></p>
            <p>Email: <code class="bg-gray-100 px-2 py-1 rounded">admin@olika.com</code></p>
            <p>Senha: <code class="bg-gray-100 px-2 py-1 rounded">123456</code></p>
        </div>
    </div>
</body>
</html>