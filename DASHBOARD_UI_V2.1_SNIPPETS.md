# 🧩 OLIKA DASHBOARD UI v2.1 - Snippets Prontos

## 📋 Índice
1. [Filtros e Cabeçalhos](#filtros-e-cabeçalhos)
2. [Cards de Produto/Cliente](#cards-de-produtocliente)
3. [Grid de Cards](#grid-de-cards)
4. [PDV Layout](#pdv-layout)
5. [Paginação](#paginação)
6. [Formulários](#formulários)
7. [Tabelas](#tabelas)
8. [Estatísticas](#estatísticas)

---

## 🔸 Filtros e Cabeçalhos

### Barra de Filtros Simples
```blade
<div class="filter-bar mb-4">
    <x-input placeholder="Buscar..." class="flex-1" />
    <x-button variant="primary">Novo</x-button>
</div>
```

### Barra de Filtros Completa
```blade
<div class="filter-bar mb-4">
    <x-input placeholder="Buscar..." class="flex-1" name="q" />
    <select class="h-10 px-3 border border-gray-300 rounded-md">
        <option>Todos</option>
        <option>Ativos</option>
        <option>Inativos</option>
    </select>
    <x-button variant="primary">Buscar</x-button>
    <x-button variant="outline">Limpar</x-button>
</div>
```

---

## 🔸 Cards de Produto/Cliente

### Card de Produto Simples
```blade
<x-card title="{{ $product->name }}">
    <img src="{{ $product->cover_image ?? '/img/placeholder.png' }}" alt="Produto">
    <div class="title">{{ $product->name }}</div>
    <div class="text-orange-600 font-semibold text-sm">
        R$ {{ number_format($product->price, 2, ',', '.') }}
    </div>
    <div class="flex gap-2 mt-auto">
        <x-button size="sm">Editar</x-button>
        <x-button size="sm" variant="outline">Duplicar</x-button>
    </div>
</x-card>
```

### Card de Produto com Variantes
```blade
<div class="card-slim">
    @if($product->cover_image)
        <img src="{{ asset('storage/' . $product->cover_image) }}" alt="{{ $product->name }}">
    @endif
    <div class="title">{{ $product->name }}</div>
    <div class="text-orange-600 font-semibold text-base">
        R$ {{ number_format($product->price, 2, ',', '.') }}
    </div>
    @if($product->stock !== null)
        <div class="text-sm text-gray-600">Estoque: {{ $product->stock }}</div>
    @endif
    <div class="flex gap-2 mt-auto">
        <x-button size="sm" variant="primary">Editar</x-button>
        <x-button size="sm" variant="outline">Duplicar</x-button>
    </div>
</div>
```

---

## 🔸 Grid de Cards

### Grid Responsiva Padrão
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @foreach($products as $product)
        <x-card title="{{ $product->name }}">
            {{-- Conteúdo do card --}}
        </x-card>
    @endforeach
</div>
```

### Grid com Classe Utilitária
```blade
<div class="grid-responsive">
    @foreach($items as $item)
        <x-card>{{ $item->name }}</x-card>
    @endforeach
</div>
```

---

## 🔸 PDV Layout

### Layout de 3 Painéis (PDV)
```blade
<div class="pdv-grid">
    <!-- Painel 1: Itens do Pedido -->
    <div class="card p-5 space-y-4">
        <h3 class="font-semibold text-lg mb-4">Itens do Pedido</h3>
        <div id="pdv-items-list" class="space-y-2">
            {{-- Itens aqui --}}
        </div>
        <div class="border-t pt-4">
            <div class="flex justify-between font-semibold">
                <span>Total:</span>
                <span id="summary-total" class="text-orange-600">R$ 0,00</span>
            </div>
        </div>
    </div>

    <!-- Painel 2: Cliente -->
    <div class="card p-5 space-y-4">
        <h3 class="font-semibold text-lg mb-4">Cliente</h3>
        <x-input placeholder="Buscar cliente..." id="customer-search" />
        {{-- Formulário do cliente --}}
    </div>

    <!-- Painel 3: Produtos -->
    <div class="card p-5 space-y-4">
        <h3 class="font-semibold text-lg mb-4">Produtos</h3>
        <x-input placeholder="Buscar produto..." id="product-search" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto">
            @foreach($products as $product)
                <button type="button" class="product-quick-add">
                    <p class="font-semibold">{{ $product->name }}</p>
                    <p class="product-price-display">R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                </button>
            @endforeach
        </div>
    </div>
</div>
```

---

## 🔸 Paginação

### Paginação Simples
```blade
<x-pagination :items="$products" />
```

### Paginação com Componente Direto
```blade
<nav class="pagination mt-6">
    {{ $items->onEachSide(1)->links('vendor.pagination.compact') }}
</nav>
```

---

## 🔸 Formulários

### Formulário Simples
```blade
<form class="space-y-4">
    <div class="form-row">
        <label class="text-sm font-medium w-32">Nome:</label>
        <x-input name="name" placeholder="Digite o nome" class="flex-1" />
    </div>
    
    <div class="form-row">
        <label class="text-sm font-medium w-32">Email:</label>
        <x-input type="email" name="email" placeholder="email@exemplo.com" class="flex-1" />
    </div>
    
    <div class="form-row">
        <x-button type="submit" variant="primary">Salvar</x-button>
        <x-button type="button" variant="outline">Cancelar</x-button>
    </div>
</form>
```

### Formulário com Grid
```blade
<form class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-2">Nome</label>
            <x-input name="name" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <x-input type="email" name="email" />
        </div>
    </div>
    <div class="flex gap-2">
        <x-button type="submit" variant="primary">Salvar</x-button>
        <x-button type="button" variant="outline">Cancelar</x-button>
    </div>
</form>
```

---

## 🔸 Tabelas

### Tabela Simples
```blade
<div class="overflow-x-auto">
    <table class="table w-full">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->email }}</td>
                    <td>
                        <x-button size="sm" variant="outline">Editar</x-button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

### Tabela Responsiva (Mobile)
```blade
<div class="overflow-x-auto">
    <table class="table w-full" data-mobile-card="true">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td data-label="Nome">{{ $item->name }}</td>
                    <td data-label="Email">{{ $item->email }}</td>
                    <td data-label="Telefone">{{ $item->phone }}</td>
                    <td data-label="Ações">
                        <x-button size="sm">Editar</x-button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

---

## 🔸 Estatísticas

### Card de Estatística
```blade
<div class="stat-card">
    <div class="value">R$ 1.503,19</div>
    <div class="label">Total Processado</div>
</div>
```

### Grid de Estatísticas
```blade
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="value">R$ 1.503,19</div>
        <div class="label">Total Processado</div>
    </div>
    <div class="stat-card">
        <div class="value">13</div>
        <div class="label">Transações</div>
    </div>
    <div class="stat-card">
        <div class="value">8</div>
        <div class="label">Pedidos Hoje</div>
    </div>
    <div class="stat-card">
        <div class="value">R$ 187,90</div>
        <div class="label">Ticket Médio</div>
    </div>
</div>
```

---

## 📝 Notas de Uso

### Ordem de CSS (já configurada no layout)
1. `dashboard.css` - Base Tailwind
2. `admin-bridge.css` - Tema base
3. `layout-fixes.css` - Correções estruturais
4. `dashboard-fixes-v2.css` - Pacote global v2.1
5. `modals.css` - Modais

### Classes Utilitárias
- `.grid-responsive` - Grid automática (1-4 colunas)
- `.pdv-grid` - Grid específica para PDV (3 colunas em XL)
- `.filter-bar` - Barra de filtros responsiva
- `.form-row` - Linha de formulário alinhada
- `.card-slim` - Card compacto padronizado
- `.stat-card` - Card de estatística

### Breakpoints
- Mobile: < 640px (1 coluna)
- Tablet: 640px - 1024px (2 colunas)
- Desktop: 1024px - 1280px (3 colunas)
- Large: > 1280px (4 colunas)

---

**Versão:** 2.1  
**Data:** 30/11/2025  
**Última atualização:** Estrutura final implementada

