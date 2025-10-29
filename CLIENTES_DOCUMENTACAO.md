# 👥 Páginas de Clientes - Sistema Olika Dashboard

## 📋 Visão Geral

Este documento descreve as páginas de clientes implementadas no sistema Olika Dashboard, utilizando os componentes Blade padronizados.

## 📁 Estrutura de Arquivos

```
resources/views/dash/pages/customers/
├── index.blade.php    # Lista de clientes
├── show.blade.php     # Visualização de cliente
└── form.blade.php     # Cadastro/Edição de cliente
```

## 🧩 Páginas Implementadas

### 1. **`customers/index.blade.php` - Lista de Clientes**

**Funcionalidades:**
- Lista todos os clientes em tabela responsiva
- Exibe informações essenciais: nome, telefone, fiado, última compra
- Badges coloridos para status do fiado
- Ações: Ver e Editar cliente
- Estado vazio com mensagem amigável

**Componentes Utilizados:**
- `<x-alert>` - Mensagens de sucesso/erro
- `<x-card>` - Container para estado vazio
- `<x-badge>` - Indicadores de fiado (danger/success)
- Botões padronizados para ações

**Campos da Tabela:**
| Campo | Descrição | Componente |
|-------|-----------|------------|
| Nome | Nome completo do cliente | Texto simples |
| Telefone | Número de telefone | Texto simples |
| Fiado | Valor em aberto | `<x-badge>` colorido |
| Última Compra | Data do último pedido | Data formatada |
| Ações | Ver/Editar | Links com ícones |

### 2. **`customers/show.blade.php` - Visualização de Cliente**

**Funcionalidades:**
- Exibe dados completos do cliente
- Mostra histórico de pedidos em tabela
- Badge de fiado com destaque visual
- Botão para editar cliente
- Estado vazio para clientes sem pedidos

**Componentes Utilizados:**
- `<x-card>` - Containers para dados e pedidos
- `<x-badge>` - Status do fiado e pedidos
- Botões padronizados

**Seções:**
1. **Cabeçalho**: Nome, telefone e botão editar
2. **Dados do Cliente**: Informações pessoais e fiado
3. **Histórico de Pedidos**: Tabela com pedidos recentes

### 3. **`customers/form.blade.php` - Cadastro/Edição**

**Funcionalidades:**
- Formulário unificado para criar e editar
- Validação com exibição de erros
- Campos obrigatórios e opcionais
- Botões de ação dinâmicos

**Componentes Utilizados:**
- `<x-alert>` - Exibição de erros de validação
- `<x-card>` - Container do formulário
- `<x-form-group>` - Grupos de campos com labels
- `<x-input>` - Campos de entrada padronizados
- `<x-button>` - Botões de ação

**Campos do Formulário:**
| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| Nome | Text | ✅ | Nome completo |
| Telefone | Text | ❌ | Número de telefone |
| E-mail | Email | ❌ | Endereço de e-mail |
| Endereço | Text | ❌ | Endereço completo |
| CPF | Text | ❌ | CPF do cliente |
| Fiado | Number | ❌ | Valor em aberto |

## 🎨 Recursos Visuais

### **Badges de Fiado**
```blade
@if(($customer->fiado ?? 0) > 0)
  <x-badge type="danger">R$ {{ number_format($customer->fiado, 2, ',', '.') }}</x-badge>
@else
  <x-badge type="success">R$ 0,00</x-badge>
@endif
```

### **Estados Vazios**
- **Sem clientes**: Ícone de usuários + mensagem explicativa
- **Sem pedidos**: Ícone de carrinho + mensagem explicativa

### **Tabelas Responsivas**
- Overflow horizontal em telas pequenas
- Hover effects nas linhas
- Cabeçalhos com fundo cinza
- Bordas sutis entre linhas

## 🔧 Controller Esperado

### **CustomersController**

```php
class CustomersController extends Controller
{
    public function index()
    {
        $customers = Customer::with('last_order')->latest()->paginate(20);
        return view('dash.pages.customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load('orders');
        return view('dash.pages.customers.show', compact('customer'));
    }

    public function create()
    {
        return view('dash.pages.customers.form');
    }

    public function edit(Customer $customer)
    {
        return view('dash.pages.customers.form', compact('customer'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string|max:500',
            'cpf' => 'nullable|string|max:14',
            'fiado' => 'nullable|numeric|min:0',
        ]);

        Customer::create($data);
        return redirect()->route('dashboard.customers.index')
            ->with('status', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string|max:500',
            'cpf' => 'nullable|string|max:14',
            'fiado' => 'nullable|numeric|min:0',
        ]);

        $customer->update($data);
        return redirect()->route('dashboard.customers.show', $customer)
            ->with('status', 'Cliente atualizado com sucesso!');
    }
}
```

## 🛣️ Rotas Necessárias

```php
// routes/web.php
Route::domain('dashboard.menuolika.com.br')->middleware('auth')->group(function () {
    Route::resource('customers', CustomersController::class)->names([
        'index' => 'dashboard.customers.index',
        'create' => 'dashboard.customers.create',
        'store' => 'dashboard.customers.store',
        'show' => 'dashboard.customers.show',
        'edit' => 'dashboard.customers.edit',
        'update' => 'dashboard.customers.update',
        'destroy' => 'dashboard.customers.destroy',
    ]);
});
```

## 📊 Modelo de Dados

### **Customer Model**
```php
class Customer extends Model
{
    protected $fillable = [
        'nome', 'telefone', 'email', 'endereco', 'cpf', 'fiado'
    ];

    protected $casts = [
        'fiado' => 'decimal:2',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function last_order()
    {
        return $this->hasOne(Order::class)->latest();
    }
}
```

## 🎯 Funcionalidades Especiais

### **Sistema de Fiado**
- **Visualização**: Badges coloridos (vermelho para débito, verde para zerado)
- **Gestão**: Campo numérico no formulário
- **Controle**: Validação para valores não negativos

### **Histórico de Pedidos**
- **Relacionamento**: Cliente possui muitos pedidos
- **Último Pedido**: Relacionamento especial para exibir data
- **Status**: Badges coloridos para status dos pedidos

### **Validação Inteligente**
- **Campos Obrigatórios**: Apenas nome é obrigatório
- **Formatação**: CPF, telefone e e-mail com placeholders
- **Feedback**: Exibição de erros em lista organizada

## 📱 Responsividade

### **Mobile First**
- Tabelas com scroll horizontal
- Botões empilhados em telas pequenas
- Formulários em coluna única
- Espaçamentos otimizados

### **Desktop**
- Layout em grid para formulários
- Tabelas com largura completa
- Botões lado a lado
- Hover effects

## 🚀 Benefícios Alcançados

✅ **Consistência Visual**: Todas as páginas seguem o mesmo padrão
✅ **Componentes Reutilizáveis**: Uso eficiente dos componentes Blade
✅ **UX Otimizada**: Estados vazios e feedback visual
✅ **Responsividade**: Funciona em todos os dispositivos
✅ **Manutenibilidade**: Código limpo e organizado
✅ **Acessibilidade**: Labels e estrutura semântica

## 📋 Checklist de Implementação

- [x] Página de listagem (`index.blade.php`)
- [x] Página de visualização (`show.blade.php`)
- [x] Página de formulário (`form.blade.php`)
- [x] Componentes Blade integrados
- [x] Estados vazios implementados
- [x] Validação de formulários
- [x] Sistema de fiado funcional
- [x] Histórico de pedidos
- [x] Responsividade completa
- [x] Documentação completa

---

**Sistema Olika Dashboard** - Módulo de Clientes v1.0
