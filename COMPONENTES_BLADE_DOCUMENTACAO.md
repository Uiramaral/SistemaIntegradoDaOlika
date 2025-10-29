# 📦 Componentes Blade - Sistema Olika Dashboard

## 🎯 Visão Geral

Este documento descreve todos os componentes Blade reutilizáveis criados para o sistema Olika Dashboard. Os componentes seguem o padrão Laravel Blade e utilizam Tailwind CSS para estilização.

## 📁 Estrutura de Arquivos

```
resources/views/components/
├── alert.blade.php          # Mensagens de alerta
├── badge.blade.php          # Indicadores de status
├── button.blade.php         # Botões padronizados
├── card.blade.php           # Container genérico
├── card-metric.blade.php    # Cards de métricas (KPIs)
├── form-group.blade.php     # Grupo de formulário
└── input.blade.php          # Campo de entrada
```

## 🧩 Componentes Disponíveis

### 1. `<x-alert>` - Mensagens de Alerta

**Propósito**: Exibir mensagens de sucesso, erro, informação e aviso.

**Props**:
- `type` (string): `success`, `error`, `info`, `warning` (padrão: `success`)

**Exemplo de Uso**:
```blade
<x-alert type="success">Operação realizada com sucesso!</x-alert>
<x-alert type="error">Erro ao processar solicitação.</x-alert>
<x-alert type="info">Informação importante.</x-alert>
<x-alert type="warning">Atenção necessária.</x-alert>
```

### 2. `<x-badge>` - Indicadores de Status

**Propósito**: Exibir badges coloridos para status e contadores.

**Props**:
- `type` (string): `success`, `warning`, `danger`, `info`, `gray` (padrão: `info`)

**Exemplo de Uso**:
```blade
<x-badge type="success">Ativo</x-badge>
<x-badge type="warning">Pendente</x-badge>
<x-badge type="danger">Inativo</x-badge>
<x-badge type="info">15</x-badge>
```

### 3. `<x-button>` - Botões Padronizados

**Propósito**: Botões com estilos consistentes e variantes.

**Props**:
- `variant` (string): `primary`, `secondary`, `success`, `danger`, `warning`, `info`, `outline` (padrão: `primary`)
- `size` (string): `sm`, `md`, `lg` (padrão: `md`)
- `type` (string): `button`, `submit`, `reset` (padrão: `button`)

**Exemplo de Uso**:
```blade
<x-button variant="primary" size="lg">Salvar</x-button>
<x-button variant="danger" type="submit">Excluir</x-button>
<x-button variant="outline" size="sm">Cancelar</x-button>
```

### 4. `<x-card>` - Container Genérico

**Propósito**: Container com padding e sombra para agrupar conteúdo.

**Props**: Aceita todos os atributos HTML padrão

**Exemplo de Uso**:
```blade
<x-card>
    <h2>Título</h2>
    <p>Conteúdo do card</p>
</x-card>

<x-card class="mb-4">
    <h2>Card com margem</h2>
</x-card>
```

### 5. `<x-card-metric>` - Cards de Métricas (KPIs)

**Propósito**: Exibir métricas importantes do dashboard com cores específicas.

**Props**:
- `color` (string): `orange`, `green`, `blue`, `purple`, `gray` (padrão: `gray`)
- `value` (string): Valor a ser exibido
- `label` (string): Rótulo descritivo

**Exemplo de Uso**:
```blade
<x-card-metric color="orange" value="123" label="Total de Pedidos" />
<x-card-metric color="green" value="R$ 1.500,00" label="Faturamento" />
<x-card-metric color="blue" value="45" label="Novos Clientes" />
<x-card-metric color="purple" value="R$ 85,50" label="Ticket Médio" />
```

### 6. `<x-form-group>` - Grupo de Formulário

**Propósito**: Agrupar labels, inputs e mensagens de erro de forma consistente.

**Props**:
- `label` (string): Texto do label
- `required` (boolean): Se o campo é obrigatório
- `error` (string): Mensagem de erro

**Exemplo de Uso**:
```blade
<x-form-group label="Nome do Produto" required>
    <x-input name="name" value="{{ old('name') }}" />
</x-form-group>

<x-form-group label="E-mail" error="{{ $errors->first('email') }}">
    <x-input type="email" name="email" />
</x-form-group>
```

### 7. `<x-input>` - Campo de Entrada

**Propósito**: Campos de entrada padronizados com estilos consistentes.

**Props**:
- `placeholder` (string): Texto placeholder
- `value` (string): Valor inicial
- `error` (string): Mensagem de erro (adiciona borda vermelha)

**Exemplo de Uso**:
```blade
<x-input name="product_name" placeholder="Digite o nome do produto" />
<x-input type="email" name="email" value="{{ old('email') }}" />
<x-input type="number" name="price" step="0.01" />
```

## 🎨 Paleta de Cores

### Cores dos Cards de Métricas
- **Orange**: `text-orange-600` - Para pedidos e ações principais
- **Green**: `text-green-600` - Para faturamento e sucessos
- **Blue**: `text-blue-600` - Para clientes e informações
- **Purple**: `text-purple-600` - Para métricas especiais
- **Gray**: `text-gray-600` - Padrão neutro

### Cores dos Badges
- **Success**: `bg-green-100 text-green-800` - Status positivo
- **Warning**: `bg-yellow-100 text-yellow-800` - Atenção
- **Danger**: `bg-red-100 text-red-800` - Erro ou inativo
- **Info**: `bg-blue-100 text-blue-800` - Informação neutra
- **Gray**: `bg-gray-100 text-gray-800` - Neutro

### Cores dos Botões
- **Primary**: `bg-orange-600 hover:bg-orange-700` - Ação principal
- **Secondary**: `bg-gray-600 hover:bg-gray-700` - Ação secundária
- **Success**: `bg-green-600 hover:bg-green-700` - Confirmação
- **Danger**: `bg-red-600 hover:bg-red-700` - Exclusão
- **Warning**: `bg-yellow-600 hover:bg-yellow-700` - Atenção
- **Info**: `bg-blue-600 hover:bg-blue-700` - Informação
- **Outline**: `border border-gray-300 hover:bg-gray-50` - Contorno

## 📝 Exemplos de Implementação

### Dashboard com Métricas
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-card-metric color="orange" value="{{ $totalPedidos }}" label="Total de Pedidos" />
    <x-card-metric color="green" value="R$ {{ number_format($faturamento, 2, ',', '.') }}" label="Faturamento" />
    <x-card-metric color="blue" value="{{ $novosClientes }}" label="Novos Clientes" />
    <x-card-metric color="purple" value="R$ {{ number_format($ticketMedio, 2, ',', '.') }}" label="Ticket Médio" />
</div>
```

### Formulário Completo
```blade
<x-card>
    <h2 class="text-xl font-bold mb-4">Novo Produto</h2>
    <form method="POST">
        @csrf
        <x-form-group label="Nome do Produto" required>
            <x-input name="name" value="{{ old('name') }}" placeholder="Digite o nome" />
        </x-form-group>
        
        <x-form-group label="Preço" required>
            <x-input type="number" name="price" step="0.01" value="{{ old('price') }}" />
        </x-form-group>
        
        <div class="flex gap-2">
            <x-button variant="primary" type="submit">Salvar</x-button>
            <x-button variant="outline" type="button">Cancelar</x-button>
        </div>
    </form>
</x-card>
```

### Lista com Badges
```blade
<x-card>
    <h3 class="text-lg font-semibold mb-4">Status dos Pedidos</h3>
    <div class="space-y-2">
        @foreach($statusPedidos as $status => $quantidade)
            <div class="flex justify-between">
                <span>{{ ucfirst($status) }}</span>
                <x-badge type="info">{{ $quantidade }}</x-badge>
            </div>
        @endforeach
    </div>
</x-card>
```

## 🔧 Customização

### Adicionando Novas Cores
Para adicionar novas cores aos componentes, edite os arrays `$colors`, `$classes`, `$styles` ou `$variants` nos respectivos arquivos de componente.

### Estendendo Componentes
Os componentes podem ser estendidos usando `@props` e `$attributes->merge()` para aceitar classes CSS adicionais.

## 📋 Checklist de Uso

- [ ] Usar `<x-card>` para agrupar conteúdo relacionado
- [ ] Usar `<x-card-metric>` para KPIs do dashboard
- [ ] Usar `<x-badge>` para status e contadores
- [ ] Usar `<x-button>` para todas as ações
- [ ] Usar `<x-form-group>` para formulários
- [ ] Usar `<x-input>` para campos de entrada
- [ ] Usar `<x-alert>` para mensagens de feedback
- [ ] Manter consistência nas cores e tamanhos
- [ ] Testar responsividade em diferentes telas

## 🚀 Benefícios

✅ **Consistência Visual**: Todos os componentes seguem o mesmo padrão de design
✅ **Reutilização**: Componentes podem ser usados em qualquer view
✅ **Manutenibilidade**: Mudanças centralizadas nos arquivos de componente
✅ **Responsividade**: Todos os componentes são responsivos
✅ **Acessibilidade**: Componentes seguem boas práticas de acessibilidade
✅ **Performance**: Componentes otimizados para renderização rápida

---

**Sistema Olika Dashboard** - Componentes Blade v1.0
