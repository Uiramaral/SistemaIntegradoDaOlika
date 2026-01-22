# Especificação de Design - Dashboard Pixel-Perfect

## 1. Estrutura Geral

### Sidebar (Menu Lateral)
- **Fundo**: Azul marinho escuro/preto (`#1e293b` ou similar)
- **Largura**: 16rem (256px)
- **Logo**: Topo com "olika" em negrito + subdomínio abaixo
- **Item Ativo**: Fundo azul vibrante (`#3b82f6` ou similar) com cantos arredondados
- **Texto**: Branco para itens não selecionados
- **Seções**: Menu Principal, Produtos, Marketing, Integrações, Outros, Master
- **Botão Sair**: Parte inferior

### Área Principal
- **Fundo**: Branco (`#ffffff`)
- **Breadcrumbs**: "Principal > [Página]" em azul
- **Título**: Fonte grande, negrito, preta
- **Subtítulo**: Texto menor, cinza claro

## 2. Dashboard - Cards de Métricas

### Card Faturamento
- **Ícone**: Cifrão ($) verde claro em círculo verde
- **Label**: "Faturamento" em cinza escuro
- **Valor**: "R$ 31.760" em fonte grande e negrito, preta
- **Fundo**: Branco com sombra sutil
- **Bordas**: Arredondadas

### Card Pedidos
- **Ícone**: Prancheta/documento azul claro em círculo azul
- **Label**: "Pedidos" em cinza escuro
- **Valor**: "203" em fonte grande e negrito, preta
- **Fundo**: Branco com sombra sutil
- **Bordas**: Arredondadas

### Card Clientes
- **Ícone**: Duas pessoas (silhuetas) roxo claro em círculo roxo
- **Label**: "Clientes" em cinza escuro
- **Valor**: "45" em fonte grande e negrito, preta
- **Fundo**: Branco com sombra sutil
- **Bordas**: Arredondadas

## 3. Cores Principais

### Cores Primárias
- **Azul Primário**: `#3b82f6` (botões, links, item ativo)
- **Verde**: `#10b981` (status ativo, ícone faturamento)
- **Roxo**: `#8b5cf6` (ícone clientes)
- **Azul Claro**: `#60a5fa` (ícone pedidos)

### Cores Neutras
- **Branco**: `#ffffff` (fundo principal, cards)
- **Preto**: `#000000` ou `#1f2937` (títulos, valores)
- **Cinza Escuro**: `#4b5563` (textos secundários)
- **Cinza Claro**: `#9ca3af` (subtítulos, placeholders)
- **Cinza Muito Claro**: `#f3f4f6` (bordas, fundos secundários)

### Sidebar
- **Fundo**: `#1e293b` ou `#0f172a` (azul marinho escuro)
- **Texto**: `#ffffff` (branco)
- **Item Ativo**: `#3b82f6` (azul vibrante)
- **Borda**: `#334155` (cinza azulado)

## 4. Tipografia

### Fontes
- **Família**: Inter (sans-serif)
- **Título Principal**: 24px, bold, preto
- **Subtítulo**: 14px, regular, cinza claro
- **Valores de Métricas**: 32px, bold, preto
- **Labels**: 14px, medium, cinza escuro
- **Texto Corpo**: 14px, regular, preto/cinza

### Espaçamentos
- **Padding Cards**: 24px
- **Gap entre Cards**: 24px
- **Margin Seções**: 32px
- **Border Radius**: 8px (cards, botões)

## 5. Componentes

### Botões
- **Primário**: Fundo azul (`#3b82f6`), texto branco, bordas arredondadas
- **Hover**: Opacidade 90%
- **Padding**: 12px 24px

### Tabelas
- **Fundo Linhas**: Branco alternado
- **Cabeçalho**: Texto maiúsculo, cinza escuro
- **Bordas**: Cinza claro
- **Hover**: Fundo cinza muito claro

### Status Tags
- **Confirmado/Ativo**: Fundo verde claro, texto branco
- **Pendente**: Fundo cinza claro, texto cinza escuro
- **Cancelado**: Fundo vermelho claro, texto branco
- **Formato**: Pílula (border-radius alto)

## 6. Personalização SaaS

### Variáveis CSS Customizáveis
- Logo (imagem)
- Favicon
- Cores do menu lateral (--sidebar-background, --sidebar-accent)
- Cores internas (--primary, --success, etc.)
- Fontes (--font-family)
- Hero do cardápio digital (imagem de destaque)

## 7. Funcionalidades

### Dashboard
- Seletor de período (calendário dropdown)
- Botão Exportar
- Filtros de período (Últimos 3 meses, 30 dias, 7 dias)
- Tabela de pedidos recentes
- Seções de Produtos e Categorias

### Navegação
- Menu lateral retrátil (mobile)
- Breadcrumbs
- Links ativos destacados
