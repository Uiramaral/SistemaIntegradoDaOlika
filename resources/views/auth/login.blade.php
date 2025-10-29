<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OLIKA Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: { DEFAULT: "hsl(var(--primary))", foreground: "hsl(var(--primary-foreground))" },
                        secondary: { DEFAULT: "hsl(var(--secondary))", foreground: "hsl(var(--secondary-foreground))" },
                        destructive: { DEFAULT: "hsl(var(--destructive))", foreground: "hsl(var(--destructive-foreground))" },
                        muted: { DEFAULT: "hsl(var(--muted))", foreground: "hsl(var(--muted-foreground))" },
                        accent: { DEFAULT: "hsl(var(--accent))", foreground: "hsl(var(--accent-foreground))" },
                        popover: { DEFAULT: "hsl(var(--popover))", foreground: "hsl(var(--popover-foreground))" },
                        card: { DEFAULT: "hsl(var(--card))", foreground: "hsl(var(--card-foreground))" },
                    },
                    borderRadius: { lg: "var(--radius)", md: "calc(var(--radius) - 2px)", sm: "calc(var(--radius) - 4px)" },
                },
            },
        }
    </script>
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96%;
            --secondary-foreground: 222.2 84% 4.9%;
            --muted: 210 40% 96%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96%;
            --accent-foreground: 222.2 84% 4.9%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
            --radius: 0.5rem;
        }
        body { background-color: hsl(var(--background)); color: hsl(var(--foreground)); }
        * { border-color: hsl(var(--border)); }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center bg-background p-4">
        <div class="w-full max-w-md">
            <!-- Card de Login -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold tracking-tight mb-2">OLIKA</h1>
                        <p class="text-muted-foreground">Dashboard Administrativo</p>
                    </div>
        </div>
                <div class="p-6 pt-0 space-y-6">
        <!-- Mensagens de Feedback -->
        @if(session('error'))
                        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
                        <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
            </div>
        @endif

                    @if(isset($errors) && $errors->any())
                        <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulário de Login -->
                    <form method="POST" action="{{ route('auth.login') }}" class="space-y-4">
            @csrf
            
                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="email">E-mail</label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    required
                                autofocus
                    value="{{ old('email') }}"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="seu@email.com"
                >
            </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium" for="password">Senha</label>
                <input 
                    id="password" 
                    name="password" 
                    type="password" 
                    required
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="••••••••"
                >
            </div>

                        <div class="flex items-center space-x-2">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            >
                            <label for="remember" class="text-sm text-muted-foreground">
                                Lembrar-me
                            </label>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                Entrar
            </button>
        </form>

        <!-- Links Adicionais -->
                    <div class="text-center text-sm text-muted-foreground">
                        @if(Route::has('register.form'))
                            <p>
                Não tem uma conta? 
                                <a href="{{ route('register.form') }}" class="text-primary hover:underline font-medium">
                    Registrar-se
                </a>
            </p>
                        @endif
                    </div>
                </div>
        </div>

        <!-- Informações do Sistema -->
            <div class="mt-6 text-center text-xs text-muted-foreground">
                <p>Sistema OLIKA Dashboard v1.0</p>
                <p>© {{ date('Y') }} Todos os direitos reservados</p>
            </div>
        </div>
    </div>
</body>
</html>
