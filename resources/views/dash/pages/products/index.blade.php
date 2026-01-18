@extends('dash.layouts.app')

@section('title', 'Produtos - OLIKA Painel')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <h1 class="text-3xl font-bold tracking-tight">Produtos</h1>
                                    <p class="text-muted-foreground">Gerencie o cardápio do seu restaurante</p>
                                </div>
                                <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-4 w-4">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5v14"></path>
                                    </svg>
                                    Novo Produto
                                </button>
                            </div>
                            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                                <div class="flex flex-col space-y-1.5 p-6">
                                    <div class="relative">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.3-4.3"></path>
                                        </svg>
                                        <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10" placeholder="Buscar produtos..." value="">
                                    </div>
                                </div>
                                <div class="p-6 pt-0">
                                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
                                            <div class="p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-accent flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-accent-foreground">
                                                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                            <path d="M12 22V12"></path>
                                                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                                            <path d="m7.5 4.27 9 5.15"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between gap-2 mb-2">
                                                            <h3 class="font-semibold truncate">Pizza Margherita</h3>
                                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary text-primary-foreground hover:bg-primary/80">Ativo</div>
                                                        </div>
                                                        <p class="text-sm text-muted-foreground mb-2">Pizzas</p>
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-lg font-bold text-primary">R$ 45,00</span>
                                                            <span class="text-sm text-muted-foreground">Estoque: 25</span>
                                                        </div>
                                                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 w-full mt-4">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
                                            <div class="p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-accent flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-accent-foreground">
                                                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                            <path d="M12 22V12"></path>
                                                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                                            <path d="m7.5 4.27 9 5.15"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between gap-2 mb-2">
                                                            <h3 class="font-semibold truncate">Hambúrguer Artesanal</h3>
                                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary text-primary-foreground hover:bg-primary/80">Ativo</div>
                                                        </div>
                                                        <p class="text-sm text-muted-foreground mb-2">Lanches</p>
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-lg font-bold text-primary">R$ 32,00</span>
                                                            <span class="text-sm text-muted-foreground">Estoque: 18</span>
                                                        </div>
                                                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 w-full mt-4">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
                                            <div class="p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-accent flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-accent-foreground">
                                                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                            <path d="M12 22V12"></path>
                                                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                                            <path d="m7.5 4.27 9 5.15"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between gap-2 mb-2">
                                                            <h3 class="font-semibold truncate">Refrigerante 350ml</h3>
                                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-primary text-primary-foreground hover:bg-primary/80">Ativo</div>
                                                        </div>
                                                        <p class="text-sm text-muted-foreground mb-2">Bebidas</p>
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-lg font-bold text-primary">R$ 6,00</span>
                                                            <span class="text-sm text-muted-foreground">Estoque: 150</span>
                                                        </div>
                                                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 w-full mt-4">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border bg-card text-card-foreground shadow-sm hover:shadow-md transition-shadow">
                                            <div class="p-6">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-accent flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-accent-foreground">
                                                            <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                            <path d="M12 22V12"></path>
                                                            <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                                            <path d="m7.5 4.27 9 5.15"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between gap-2 mb-2">
                                                            <h3 class="font-semibold truncate">Batata Frita</h3>
                                                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80">Inativo</div>
                                                        </div>
                                                        <p class="text-sm text-muted-foreground mb-2">Acompanhamentos</p>
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-lg font-bold text-primary">R$ 12,00</span>
                                                            <span class="text-sm text-muted-foreground">Estoque: 45</span>
                                                        </div>
                                                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 rounded-md px-3 w-full mt-4">Editar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
