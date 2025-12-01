# 🧠 OLIKA DASHBOARD UI v2.1 - Guia de Manutenção

## 📋 Índice
1. [Estrutura de Arquivos](#estrutura-de-arquivos)
2. [Ordem de CSS](#ordem-de-css)
3. [Componentes Blade](#componentes-blade)
4. [Boas Práticas](#boas-práticas)
5. [Troubleshooting](#troubleshooting)
6. [Atualizações Futuras](#atualizações-futuras)

---

## 📁 Estrutura de Arquivos

### CSS
```
public/css/
├── dashboard.css              # Base Tailwind (não modificar)
├── admin-bridge.css          # Tema base (cores, tipografia)
├── layout-fixes.css          # Correções estruturais
├── dashboard-fixes-v2.css    # ⭐ Pacote global v2.1 (PRINCIPAL)
└── modals.css                # Estilos de modais
```

### Componentes Blade
```
resources/views/components/
├── x-input.blade.php         # Input padronizado
├── x-button.blade.php        # Botão padronizado
├── x-card.blade.php          # Card padronizado
└── x-pagination.blade.php    # Paginação padronizada
```

### Paginação
```
resources/views/vendor/pagination/
└── compact.blade.php         # Template de paginação compacta
```

---

## 🎨 Ordem de CSS

**IMPORTANTE:** A ordem de carregamento é crítica. Sempre manter esta sequência:

```blade
<!-- 1. Base Tailwind -->
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ $cssVersion }}">

<!-- 2. Tema base (cores, tipografia, botões) -->
<link rel="stylesheet" href="{{ asset('css/admin-bridge.css') }}?v={{ $cssVersion }}">

<!-- 3. Correções estruturais -->
<link rel="stylesheet" href="{{ asset('css/layout-fixes.css') }}?v={{ $cssVersion }}">

<!-- 4. Pacote global de correções v2 -->
<link rel="stylesheet" href="{{ asset('css/dashboard-fixes-v2.css') }}?v={{ $cssVersion }}">

<!-- 5. Modais -->
<link rel="stylesheet" href="{{ asset('css/modals.css') }}?v={{ $cssVersion }}">
```

**Arquivos desativados (redundantes):**
- ❌ `pdv-fixes.css` - Conteúdo migrado para v2
- ❌ `dashboard-fixes.css` - Substituído por v2
- ❌ `all-styles.css` - Não usar

---

## 🧩 Componentes Blade

### x-input
**Uso:**
```blade
<x-input name="email" placeholder="Digite o email" />
<x-input type="number" name="price" value="0" />
```

**Props:**
- `type` - Tipo do input (text, email, number, etc.)
- `name` - Nome do campo
- `value` - Valor inicial
- `placeholder` - Texto placeholder

### x-button
**Uso:**
```blade
<x-button variant="primary" size="md">Salvar</x-button>
<x-button variant="outline" size="sm">Cancelar</x-button>
```

**Props:**
- `variant` - primary, secondary, outline
- `size` - sm, md, lg
- `type` - button, submit, reset

### x-card
**Uso:**
```blade
<x-card title="Título do Card">
    Conteúdo do card
</x-card>
```

**Props:**
- `title` - Título do card (opcional)
- `footer` - Conteúdo do rodapé (opcional)

### x-pagination
**Uso:**
```blade
<x-pagination :items="$products" />
```

**Props:**
- `items` - Collection paginada do Laravel

---

## ✅ Boas Práticas

### 1. Evitar !important
O pacote `dashboard-fixes-v2.css` já usa `!important` onde necessário. Evite adicionar mais.

### 2. Usar Classes Tailwind
Sempre preferir classes Tailwind utilitárias:
```blade
<!-- ✅ Correto -->
<div class="flex items-center gap-4 p-6">

<!-- ❌ Evitar -->
<div style="display: flex; align-items: center; gap: 1rem; padding: 1.5rem;">
```

### 3. Grids Responsivas
Sempre usar breakpoints progressivos:
```blade
<!-- ✅ Correto -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

<!-- ❌ Evitar -->
<div class="grid grid-cols-3">
```

### 4. Altura de Inputs/Botões
Sempre usar componentes ou classes padronizadas:
```blade
<!-- ✅ Correto -->
<x-input name="email" />
<x-button>Salvar</x-button>

<!-- ❌ Evitar -->
<input style="height: 50px;">
<button style="height: 50px;">
```

### 5. Cache Busting
Sempre incrementar versão no `.env`:
```env
APP_ASSETS_VERSION=2.1
```

E no layout:
```blade
$cssVersion = env('APP_ASSETS_VERSION', '2.1');
```

### 6. Testar Resoluções
Sempre testar em:
- 📱 Mobile: 375px, 414px
- 📱 Tablet: 768px, 1024px
- 💻 Desktop: 1366px, 1440px, 1920px

---

## 🔧 Troubleshooting

### Problema: Estilos não aplicam
**Solução:**
1. Verificar ordem de CSS no layout
2. Limpar cache do navegador (Ctrl + F5)
3. Verificar se `APP_ASSETS_VERSION` foi atualizado
4. Limpar cache Laravel: `php artisan view:clear`

### Problema: Grid quebra em mobile
**Solução:**
1. Verificar se está usando `grid-cols-1` como base
2. Adicionar breakpoints: `sm:grid-cols-2 lg:grid-cols-3`
3. Verificar se não há `grid-cols-3` fixo

### Problema: Inputs/Botões desalinhados
**Solução:**
1. Usar componentes `<x-input>` e `<x-button>`
2. Verificar se não há estilos inline sobrescrevendo
3. Verificar se `dashboard-fixes-v2.css` está carregado

### Problema: Paginação não aparece
**Solução:**
1. Verificar se está usando `<x-pagination :items="$collection" />`
2. Verificar se a collection tem `->links()` disponível
3. Verificar se `vendor/pagination/compact.blade.php` existe

### Problema: Sidebar muito larga
**Solução:**
1. Verificar se `dashboard-fixes-v2.css` está carregado
2. Sidebar deve ter `width: 16rem` (já configurado no CSS)

---

## 🔄 Atualizações Futuras

### Versão 2.2 (Planejado)
- [ ] Adicionar tema dark mode
- [ ] Melhorar animações de transição
- [ ] Adicionar mais variantes de botões
- [ ] Otimizar CSS para menor tamanho

### Checklist de Atualização
1. ✅ Atualizar `APP_ASSETS_VERSION` no `.env`
2. ✅ Atualizar versão no CSS (`/* Versão: 2.X */`)
3. ✅ Testar em todas as resoluções
4. ✅ Atualizar documentação
5. ✅ Limpar cache Laravel
6. ✅ Testar em produção

---

## 📚 Referências

- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Laravel Blade Components](https://laravel.com/docs/blade#components)
- [Guia Pixel Perfect Olika Dashboard v1.0](./ANALISE_LAYOUT_DASHBOARD.md)
- [Snippets Prontos](./DASHBOARD_UI_V2.1_SNIPPETS.md)

---

## 🆘 Suporte

Para dúvidas ou problemas:
1. Verificar este guia primeiro
2. Consultar `DASHBOARD_UI_V2.1_SNIPPETS.md`
3. Verificar logs do Laravel
4. Testar em modo de desenvolvimento

---

**Versão:** 2.1  
**Última atualização:** 30/11/2025  
**Mantido por:** Equipe Olika

