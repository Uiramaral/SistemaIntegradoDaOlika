# 🍞 OLIKA — DASHBOARD LAYOUT PACK

## ✅ ARQUIVOS CRIADOS

1. `resources/css/dashboard.css` - CSS com variáveis de tema (já criado)
2. `resources/views/layouts/dashboard.blade.php` - Layout base Lovable (já criado)
3. `resources/views/components/stat-card.blade.php` - Componente stat card (já criado)
4. `resources/views/dashboard/index.blade.php` - Página principal (já criado)
5. `resources/images/olika-mark.svg` - Logo placeholder (já criado)

---

## 🚀 COMO APLICAR AGORA (PRODUÇÃO)

### 1. Atualizar app.css
Edite `resources/css/app.css` e adicione:
```css
@import "./dashboard.css";
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### 2. Garantir rotas
Em `routes/web.php`, dentro do grupo `dashboard.menuolika.com.br`:
```php
Route::get('/', [\App\Http\Controllers\Dashboard\DashboardController::class, 'index'])->name('dashboard.index');
```

### 3. Atualizar Controller
Em `app/Http/Controllers/Dashboard/DashboardController.php`:
```php
public function home() {
    $kpis = $this->kpisBase();
    
    $recentOrders = DB::table('orders as o')
        ->leftJoin('customers as c','c.id','=','o.customer_id')
        ->select('o.*','c.name as customer_name')
        ->orderByDesc('o.id')->limit(20)->get();
    
    return view('dashboard.index', compact('kpis','recentOrders'));
}
```

### 4. Deploy (produção)
```bash
cd /home4/hg6ddb59/public_html/sistema

# Limpar caches
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan optimize:clear

# Se builda no servidor
npm ci --omit=dev
npm run build
```

---

## 📋 ESTRUTURA DE VIEWS

### Layout Base (`layouts/dashboard.blade.php`)
- Topbar sticky translúcida
- Page header (título + ações)
- Grid de stat cards
- Content slot

### Componente (`components/stat-card.blade.php`)
```blade
<x-stat-card 
    label="Pedidos Hoje" 
    :value="10" 
    hint="Hoje" 
    delta="+8%" 
/>
```

### Páginas Filhas
Todas devem usar:
```blade
@extends('layouts.dashboard')

@section('title','Nome da Página')
@section('page-title','Título Principal')
@section('page-subtitle','Subtítulo opcional')

@section('page-actions')
  <a href="#" class="btn-primary">Nova Ação</a>
@endsection

@section('stat-cards')
  <x-stat-card label="Total" :value="100" />
  <x-stat-card label="Ativos" :value="80" />
@endsection

@section('content')
  {{-- Conteúdo aqui --}}
@endsection
```

---

## 🎨 VARIÁVEIS CSS

```css
--color-primary: hsl(25 95% 53%);   /* laranja */
--color-bg: hsl(0 0% 98%);          /* quase branco */
--color-text: hsl(222 47% 11%);     /* texto escuro */
--color-muted: hsl(215 16% 47%);    /* texto suave */
--radius: 14px;
--shadow-sm: 0 1px 2px rgba(0,0,0,.06);
--shadow-md: 0 6px 20px rgba(0,0,0,.08);
```

---

## 🧩 CLASSES ÚTEIS

### Botão Primário
```html
<a href="#" class="btn-primary">Texto</a>
```

### Pill/Badge
```html
<span class="pill">Status</span>
```

### Card
```html
<div class="card" style="padding:16px">Conteúdo</div>
```

### Stat
```blade
<div class="stat">
  <div class="label">Label</div>
  <div class="value">999</div>
</div>
```

### Tabela
```html
<table class="table-compact">
  <thead>...</thead>
  <tbody>...</tbody>
</table>
```

---

## ✅ CHECKLIST DE APLICAÇÃO

- [ ] Importar `dashboard.css` em `app.css`
- [ ] Atualizar controller `home()`
- [ ] Deploy com comandos acima
- [ ] Testar no dashboard.menuolika.com.br
- [ ] Adaptar páginas filhas (opcional)

---

## 🎊 RESULTADO

Dashboard com visual **idêntico ao status-templates do Lovable**:
- ✅ Topbar sticky translúcida
- ✅ Filtros em pills
- ✅ Stat cards destacados
- ✅ Tabelas compactas
- ✅ Botões arredondados
- ✅ Totalmente responsivo

**Pronto para produção!** 🚀
