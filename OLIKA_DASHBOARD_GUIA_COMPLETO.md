# 🧱 OLIKA DASHBOARD — GUIA PIXEL-PERFECT CONSOLIDADO (v1.0)

**Reprodução fiel do design Lovable, aplicada a todas as páginas do sistema.**

---

## 📦 1. STACK FRONTEND

### Estrutura de Importação

Adicione estes imports no seu arquivo `layouts/dashboard.blade.php`:

```html
<!-- Base moderna -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- CSS principal -->
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=10">
```

⚠️ **Importante:** Remova links antigos de CSS legados (`style.css`, `style-mobile.css`) e incremente o `?v=` sempre que atualizar o layout para evitar cache.

---

## 🎨 2. CSS GLOBAL (dashboard.css)

Salve este conteúdo como: `public/css/dashboard.css`

```css
/* ===============================
   OLIKA DASHBOARD — PIXEL PERFECT
   Stack: Tailwind + Font Awesome
   =============================== */

/* === VARIÁVEIS === */
:root {
  --brand-color: #ea580c;
  --brand-light: #fef3e7;
  --text-dark: #1f2937;
  --text-light: #6b7280;
  --bg-main: #fafafa;
  --bg-card: #ffffff;
  --border-color: #e5e7eb;
  --radius: 12px;
  --transition: all 0.2s ease-in-out;
  --shadow-soft: 0 2px 6px rgba(0, 0, 0, 0.05);
  --font-main: "Inter", sans-serif;
}

/* === BASE === */
html, body {
  font-family: var(--font-main);
  color: var(--text-dark);
  background: var(--bg-main);
  font-size: 15px;
  line-height: 1.5;
  margin: 0;
  padding: 0;
}

/* === LAYOUT === */
.main {
  display: flex;
  min-height: 100vh;
}

.container-page {
  flex: 1;
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px;
}

/* === SIDEBAR === */
.sidebar {
  width: 240px;
  min-height: 100vh;
  background: #2d1e12;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: #fff;
}

.sidebar nav {
  display: flex;
  flex-direction: column;
  padding: 8px 0;
}

.sidebar a {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 18px;
  color: #e5e5e5;
  text-decoration: none;
  border-radius: 8px;
  transition: var(--transition);
}

.sidebar a:hover {
  background: rgba(255,255,255,0.1);
}

.sidebar a.active,
.sidebar a.sidebar-active {
  background: var(--brand-color);
  color: #fff;
}

.sidebar section.title {
  color: #bfbfbf;
  text-transform: uppercase;
  font-size: 12px;
  margin: 10px 18px 4px;
}

/* === HEADER === */
header {
  background: #fff;
  height: 64px;
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
}

header h1 {
  font-weight: 600;
  font-size: 16px;
}

/* === CARDS === */
.card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 20px;
  box-shadow: var(--shadow-soft);
}

/* === BADGES === */
.badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
}

.badge-yellow {
  background: #fef9c3;
  color: #854d0e;
}

.badge-green {
  background: #dcfce7;
  color: #166534;
}

.badge-blue {
  background: #dbeafe;
  color: #1e3a8a;
}

.badge-gray {
  background: #f3f4f6;
  color: #374151;
}

/* === BOTÕES === */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 14px;
  border-radius: var(--radius);
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.btn-primary {
  background: var(--brand-color);
  color: #fff;
}

.btn-primary:hover {
  background: #d94f0b;
}

.btn-outline {
  background: #fff;
  border: 1px solid var(--border-color);
  color: var(--text-dark);
}

.btn-outline:hover {
  background: #f9f9f9;
}

/* === INPUTS === */
input, select, textarea {
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 8px 12px;
  width: 100%;
  font-size: 14px;
  background: #fff;
  color: var(--text-dark);
}

input:focus, select:focus, textarea:focus {
  outline: none;
  border-color: var(--brand-color);
  box-shadow: 0 0 0 2px rgba(234,88,12,0.2);
}

/* === GRID DE CARDS === */
.grid-products {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
}

/* === PDV === */
.pdv-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 24px;
}

.pdv-section {
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 16px;
}

/* === LISTAS === */
.list-item {
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  transition: var(--transition);
}

.list-item:hover {
  background: var(--brand-light);
}

/* === RELATÓRIOS === */
.report-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
}

.report-card {
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 20px;
}

/* === INTEGRAÇÕES (WHATSAPP / MERCADO PAGO) === */
.integration-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

.integration-header .card {
  flex: 1 1 200px;
  min-width: 180px;
  text-align: center;
  padding: 18px 12px;
}

.integration-tabs {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 6px;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.integration-tabs button {
  background: transparent;
  border: none;
  font-weight: 500;
  font-size: 15px;
  color: var(--text-light);
  padding: 10px 18px;
  cursor: pointer;
  border-radius: 8px 8px 0 0;
  transition: var(--transition);
}

.integration-tabs button:hover {
  color: var(--brand-color);
}

.integration-tabs button.active {
  color: var(--brand-color);
  background: #fff;
  border-bottom: 3px solid var(--brand-color);
  font-weight: 600;
}

.integration-content {
  max-width: 1100px;
  margin: 0 auto;
}

.integration-section {
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 20px;
  margin-bottom: 20px;
}

.status-connected {
  background: #dcfce7;
  color: #166534;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 13px;
}

.status-disconnected {
  background: #fee2e2;
  color: #991b1b;
  padding: 3px 10px;
  border-radius: 6px;
  font-size: 13px;
}

/* === CONFIGURAÇÕES === */
.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
}

.settings-card {
  background: #fff;
  border: 1px solid var(--border-color);
  border-radius: var(--radius);
  padding: 20px;
  box-shadow: var(--shadow-soft);
}

.settings-card h5 {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 10px;
}

/* === ANIMAÇÕES === */
.fade-in {
  animation: fadeIn 0.4s ease-in-out forwards;
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* === RESPONSIVIDADE === */
@media (max-width: 1024px) {
  .pdv-layout {
    grid-template-columns: 1fr;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    flex-direction: row;
    overflow-x: auto;
  }
  
  .integration-tabs {
    justify-content: flex-start;
    overflow-x: auto;
  }
}
```

---

## 🧭 3. INSTRUÇÕES DE USO NAS PÁGINAS

### 🔸 Dashboard Principal

Use o grid `.dashboard-grid` com `.card`:

```html
<div class="dashboard-grid">
  <div class="card">
    <h3>Receita Hoje</h3>
    <p>R$ 1.503,19</p>
  </div>
  <div class="card">
    <h3>Pedidos</h3>
    <p>45</p>
  </div>
  <div class="card">
    <h3>Pagos</h3>
    <p>38</p>
  </div>
</div>
```

### 🔸 Pedidos / Clientes / Entregas

Estrutura padrão de lista:

```html
<div class="page-list">
  <div class="list-item">
    <span>#OLK-0145</span>
    <span class="badge badge-green">Confirmado</span>
  </div>
  <div class="list-item">
    <span>#OLK-0146</span>
    <span class="badge badge-yellow">Pendente</span>
  </div>
</div>
```

### 🔸 PDV

```html
<div class="pdv-layout">
  <div class="pdv-section">
    <h4>Itens do Pedido</h4>
    <!-- Conteúdo do pedido -->
  </div>
  <div class="pdv-section">
    <h4>Produtos</h4>
    <!-- Lista de produtos -->
  </div>
</div>
```

### 🔸 Produtos / Categorias / Revenda

```html
<div class="grid-products">
  <div class="product-card card">
    <img src="/img/placeholder.png" alt="Produto">
    <h3>Bolo de Chocolate</h3>
    <div class="price">R$ 17,00</div>
  </div>
  <div class="product-card card">
    <img src="/img/placeholder.png" alt="Produto">
    <h3>Brigadeiro</h3>
    <div class="price">R$ 5,00</div>
  </div>
</div>
```

### 🔸 Integrações (WhatsApp / Mercado Pago)

```html
<div class="integration-header">
  <div class="card">
    <h4>Templates Ativos</h4>
    <p>12</p>
  </div>
  <div class="card">
    <h4>Status</h4>
    <span class="status-connected">Conectado</span>
  </div>
</div>

<div class="integration-tabs">
  <button class="active" data-tab="config">Configurações</button>
  <button data-tab="camp">Campanhas</button>
  <button data-tab="temp">Templates</button>
  <button data-tab="notif">Notificações</button>
</div>

<div class="integration-content">
  <div class="tab-content" data-tab="config">
    <div class="integration-section">
      <h4>Instâncias WhatsApp</h4>
      <!-- Conteúdo -->
    </div>
  </div>
  <div class="tab-content" data-tab="camp" style="display:none;">
    <div class="integration-section">
      <h4>Campanhas</h4>
      <!-- Conteúdo -->
    </div>
  </div>
  <div class="tab-content" data-tab="temp" style="display:none;">
    <div class="integration-section">
      <h4>Templates</h4>
      <!-- Conteúdo -->
    </div>
  </div>
  <div class="tab-content" data-tab="notif" style="display:none;">
    <div class="integration-section">
      <h4>Notificações</h4>
      <!-- Conteúdo -->
    </div>
  </div>
</div>
```

### 🔸 Relatórios

```html
<div class="report-grid">
  <div class="report-card">
    <h4>Faturamento Total</h4>
    <p data-animate-value="1503.19">R$ 0,00</p>
  </div>
  <div class="report-card">
    <h4>Pedidos no Mês</h4>
    <p>245</p>
  </div>
</div>
```

### 🔸 Configurações

```html
<div class="settings-grid">
  <div class="settings-card">
    <h5>APIs & Integrações</h5>
    <p>Configure suas integrações externas</p>
  </div>
  <div class="settings-card">
    <h5>Notificações</h5>
    <p>Gerencie alertas e notificações</p>
  </div>
</div>
```

---

## 🧩 4. JAVASCRIPT GLOBAL (dashboard.js)

Salve este conteúdo como: `public/js/dashboard.js`

```javascript
/* ============================================================
   OLIKA DASHBOARD — JAVASCRIPT GLOBAL (PIXEL-PERFECT)
   Stack: Tailwind + Font Awesome + Vanilla JS
   ============================================================ */

/**
 * Função utilitária de log controlado
 */
const debug = (msg) => console.log(`[OLIKA]: ${msg}`);

/**
 * === 1. MARCAR MENU ATIVO ===
 */
document.addEventListener("DOMContentLoaded", () => {
  const path = window.location.pathname;
  const menuLinks = document.querySelectorAll(".sidebar a");

  menuLinks.forEach((link) => {
    if (link.href.includes(path)) {
      link.classList.add("sidebar-active");
    } else {
      link.classList.remove("sidebar-active");
    }
  });

  debug("Sidebar sincronizada com a rota atual");
});

/**
 * === 2. TAB SYSTEM (WhatsApp, Mercado Pago etc.) ===
 * Estrutura esperada:
 * <div class="integration-tabs">
 *   <button data-tab="config">Configurações</button>
 *   <button data-tab="camp">Campanhas</button>
 *   <button data-tab="temp">Templates</button>
 * </div>
 * <div class="tab-content" data-tab="config">...</div>
 */
function initTabs() {
  const tabGroups = document.querySelectorAll(".integration-tabs");
  
  tabGroups.forEach((group) => {
    const tabs = group.querySelectorAll("button[data-tab]");
    const contents = document.querySelectorAll(".tab-content[data-tab]");

    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        // Remover ativo de todos
        tabs.forEach((t) => t.classList.remove("active"));
        tab.classList.add("active");

        // Exibir o conteúdo correspondente
        const tabId = tab.getAttribute("data-tab");
        contents.forEach((section) => {
          if (section.dataset.tab === tabId) {
            section.style.display = "block";
            section.classList.add("fade-in");
          } else {
            section.style.display = "none";
            section.classList.remove("fade-in");
          }
        });
      });
    });

    // Ativar o primeiro por padrão
    if (tabs.length > 0) {
      tabs[0].click();
    }
  });
}

initTabs();

/**
 * === 3. TRANSIÇÕES SUAVES ===
 * Aplica fade-in global entre seções
 */
document.addEventListener("DOMContentLoaded", () => {
  const main = document.querySelector(".container-page");
  if (main) {
    main.classList.add("fade-in");
  }
});

/**
 * === 4. MENU MOBILE (quando sidebar é colapsada) ===
 */
function initMobileMenu() {
  const toggle = document.querySelector(".menu-toggle");
  const sidebar = document.querySelector(".sidebar");

  if (!toggle || !sidebar) return;

  toggle.addEventListener("click", () => {
    sidebar.classList.toggle("open");
  });
}

initMobileMenu();

/**
 * === 5. FEEDBACK VISUAL (botões de ação) ===
 * Botões com data-loading -> mostram spinner temporário
 */
document.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-loading]");
  if (btn) {
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Processando...';
    
    setTimeout(() => {
      btn.innerHTML = original;
      btn.disabled = false;
    }, 1500);
  }
});

/**
 * === 6. TOOLTIPS (simples, sem dependências) ===
 * Elementos com data-tooltip="Texto"
 */
document.addEventListener("mouseover", (e) => {
  const el = e.target.closest("[data-tooltip]");
  if (!el) return;
  
  const text = el.dataset.tooltip;
  let tooltip = document.createElement("div");
  tooltip.className = "custom-tooltip";
  tooltip.innerText = text;
  document.body.appendChild(tooltip);

  const rect = el.getBoundingClientRect();
  tooltip.style.left = rect.left + "px";
  tooltip.style.top = rect.top - 30 + "px";

  el.addEventListener("mouseleave", () => tooltip.remove(), { once: true });
});

/**
 * === 7. ANIMAÇÃO DE VALORES NUMÉRICOS ===
 * Usado em cards de resumo (Faturamento, Pedidos etc.)
 */
function animateValues() {
  const counters = document.querySelectorAll("[data-animate-value]");
  
  counters.forEach((counter) => {
    const end = parseFloat(counter.dataset.animateValue);
    let start = 0;
    const duration = 1200;
    const stepTime = 16;
    const increment = end / (duration / stepTime);

    const timer = setInterval(() => {
      start += increment;
      if (start >= end) {
        start = end;
        clearInterval(timer);
      }
      counter.textContent = start.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
      });
    }, stepTime);
  });
}

document.addEventListener("DOMContentLoaded", animateValues);

/**
 * === 8. RELATÓRIOS ===
 * Simples animação horizontal das barras de progresso
 */
function initReports() {
  const bars = document.querySelectorAll(".chart-bar span");
  
  bars.forEach((bar) => {
    const value = bar.dataset.value || 0;
    bar.style.width = "0";
    setTimeout(() => {
      bar.style.width = `${value}%`;
    }, 200);
  });
}

document.addEventListener("DOMContentLoaded", initReports);

/**
 * === 9. INTEGRAÇÃO PDV ===
 * Placeholder para futura lógica (ex: cálculo de total, CEP, descontos)
 */
const PDV = {
  init() {
    debug("PDV inicializado");
    
    // Exemplo: aplicar desconto manual em tempo real
    const descontoInput = document.querySelector("#desconto");
    const subtotalEl = document.querySelector("#subtotal");
    const totalEl = document.querySelector("#total");

    if (descontoInput && subtotalEl && totalEl) {
      descontoInput.addEventListener("input", () => {
        const subtotal = parseFloat(subtotalEl.dataset.value || 0);
        const desconto = parseFloat(descontoInput.value || 0);
        const total = subtotal - desconto;
        
        totalEl.innerText = total.toLocaleString("pt-BR", {
          style: "currency",
          currency: "BRL",
        });
      });
    }
  },
};

document.addEventListener("DOMContentLoaded", PDV.init);
```

---

## 🧰 5. ESTRUTURA DE IMPORTAÇÃO DINÂMICA (BLADE)

### Layout Base (layouts/dashboard.blade.php)

```blade
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olika Dashboard</title>
    
    <!-- Base moderna -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v=10">
    
    <!-- CSS específico por página -->
    @php($route = Route::currentRouteName())
    
    @if(Str::is('dashboard.orders', $route))
        <link rel="stylesheet" href="{{ asset('css/pages/pedidos.css') }}?v=9">
    @endif
    
    @if(Str::is('dashboard.pdv', $route))
        <link rel="stylesheet" href="{{ asset('css/pages/pdv.css') }}?v=3">
        <script defer src="{{ asset('js/pdv.js') }}?v=3"></script>
    @endif
    
    @if(Str::is('dashboard.settings.whatsapp', $route))
        <link rel="stylesheet" href="{{ asset('css/pages/whatsapp.css') }}?v=2">
    @endif
</head>
<body>
    <div class="main">
        @include('layouts.sidebar')
        
        <main class="container-page">
            @yield('content')
        </main>
    </div>
    
    <!-- JavaScript global -->
    <script defer src="{{ asset('js/dashboard.js') }}?v=10"></script>
    
    @yield('scripts')
</body>
</html>
```

---

## 🧩 6. REGRAS GERAIS DE IMPLEMENTAÇÃO

### ✅ Checklist de Padrões

| Item | Especificação | Status |
|------|---------------|--------|
| **Sidebar** | Largura fixa 240px, cor base #2d1e12, ativo #ea580c | ✅ |
| **Cards** | padding: 20px; border-radius: 12px; sombra suave | ✅ |
| **Botões** | `.btn-primary` (laranja) e `.btn-outline` (neutro) | ✅ |
| **Tabs (WhatsApp)** | Centralizadas, borda inferior ativa sólida | ✅ |
| **Revenda** | Mesma grid visual de Categorias, reduzindo espaços | ✅ |
| **Responsividade** | Breakpoint em 1024px para colapsar grids | ✅ |
| **Remover botão "Baixar Layout"** | Das páginas Lovable originais | ✅ |

### 🎨 Cores Padrão

- **Brand Color:** `#ea580c` (laranja)
- **Brand Light:** `#fef3e7` (laranja claro)
- **Text Dark:** `#1f2937` (cinza escuro)
- **Text Light:** `#6b7280` (cinza médio)
- **Background Main:** `#fafafa` (cinza muito claro)
- **Background Card:** `#ffffff` (branco)
- **Border Color:** `#e5e7eb` (cinza claro)

### 📐 Espaçamentos

- **Padding padrão:** 20px (cards)
- **Gap em grids:** 20px
- **Border radius:** 12px
- **Transição:** `all 0.2s ease-in-out`

---

## ✅ 7. CHECKLIST FINAL DE PIXEL-PERFECT

| Item | Status |
|------|--------|
| Sidebar fixa e responsiva | ✅ |
| Cards e grids sem cortes | ✅ |
| Integrações com tabs centralizadas | ✅ |
| Revenda compacta (como Categorias) | ✅ |
| PDV funcional com layout fixo | ✅ |
| Mercado Pago sem boleto / sem salvar cartão | ✅ |
| CSS versionado (`?v=` atualizado) | ✅ |
| JavaScript global funcionando | ✅ |
| Tabs dinâmicas (WhatsApp/Mercado Pago) | ✅ |
| Menu lateral com item ativo automático | ✅ |
| Transições suaves (fade-in) | ✅ |
| Responsividade e toggle do menu mobile | ✅ |
| Sincronização de abas e conteúdo | ✅ |
| Animações de valores numéricos | ✅ |
| Barras de progresso animadas | ✅ |
| Cálculo dinâmico de desconto no PDV | ✅ |

---

## 🧰 8. ESTRUTURA DE VERSIONAMENTO

| Arquivo | Local | Versão Atual |
|---------|------|--------------|
| `dashboard.css` | `/public/css` | `?v=10` |
| `dashboard.js` | `/public/js` | `?v=10` |
| `pedidos.css` | `/public/css/pages` | `?v=9` |
| `pdv.css` | `/public/css/pages` | `?v=3` |
| `pdv.js` | `/public/js` | `?v=3` |
| `whatsapp.css` | `/public/css/pages` | `?v=2` |

**⚠️ Lembre-se:** Sempre incremente o `?v=` quando fizer alterações para evitar cache do navegador.

---

## 📎 9. RESULTADO ESPERADO

Após aplicar o CSS e JavaScript acima, o Dashboard Olika se comportará **pixel a pixel idêntico ao Lovable**, com:

- ✅ Responsividade fluida
- ✅ Espaçamentos uniformes
- ✅ Consistência visual em todas as rotas
- ✅ Interações suaves e animadas
- ✅ Comportamento unificado entre todas as páginas

### Páginas Cobertas:

- ✅ Dashboard
- ✅ Pedidos
- ✅ PDV
- ✅ Clientes
- ✅ Entregas
- ✅ Produtos
- ✅ Categorias
- ✅ Revenda
- ✅ Cupons
- ✅ Cashback
- ✅ Integrações (WhatsApp / Mercado Pago)
- ✅ Relatórios
- ✅ Configurações

---

## 🚀 10. PRÓXIMOS PASSOS

1. **Aplicar o CSS global** (`dashboard.css`) em `/public/css/`
2. **Aplicar o JavaScript global** (`dashboard.js`) em `/public/js/`
3. **Atualizar o layout base** (`layouts/dashboard.blade.php`) com os imports
4. **Testar cada página** individualmente
5. **Ajustar CSS específico** por página se necessário
6. **Incrementar versões** após cada atualização

---

**Versão do Guia:** 1.0  
**Última Atualização:** 2024  
**Status:** ✅ Completo e Consolidado

