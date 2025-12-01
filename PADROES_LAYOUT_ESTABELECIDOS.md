# Padrões de Layout Estabelecidos - Dashboard

## 📐 PADRÕES VISUAIS

### **1. Cards de Métricas**

**Padrão**:
- Padding: `p-4` (compacto)
- Título: `text-xs text-muted-foreground mb-1`
- Valor: `text-xl font-bold`
- Container: `flex items-center justify-between`
- Conteúdo: `flex-1 min-w-0` (para evitar overflow)
- Ícone (se houver): `h-5 w-5 flex-shrink-0 ml-2`
- Grid: `grid grid-cols-4 gap-3` (sempre 4 colunas na mesma linha)

**Exemplo**:
```blade
<div class="grid grid-cols-4 gap-3">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-muted-foreground mb-1">Título</p>
                    <p class="text-xl font-bold">Valor</p>
                </div>
                <svg class="h-5 w-5 text-primary flex-shrink-0 ml-2">...</svg>
            </div>
        </div>
    </div>
</div>
```

---

### **2. Grids de Conteúdo**

**Padrão para 2 Colunas**:
- `grid gap-4 lg:grid-cols-2`
- Cards organizados em `space-y-4` dentro de cada coluna

**Padrão para 4 Colunas (Cards de Métricas)**:
- `grid grid-cols-4 gap-3`
- Sempre na mesma linha em desktop

---

### **3. Campos de Input**

**Padrão**:
- Tamanho: `text-base` (para melhor usabilidade)
- Padding: `px-4 py-2.5`
- Altura: `h-11` (alinhado com botões)
- Border radius: `rounded-md`

**Botões**:
- Altura padrão: `h-10` ou `h-11` (alinhado com inputs)
- Padding: `px-4`

---

### **4. Modais/Popups**

**Padrão Estabelecido**:
```html
<div class="fixed inset-0 z-50 hidden items-center justify-center" style="background-color: rgba(0, 0, 0, 0.75);">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md mx-4 relative">
        <div class="flex items-center justify-between mb-4 p-6 pb-4">
            <h3 class="text-xl font-semibold">Título</h3>
            <button class="text-gray-400 hover:text-gray-600 transition-colors">
                <!-- Ícone X -->
            </button>
        </div>
        <div class="p-6 pt-0">
            <!-- Conteúdo -->
        </div>
    </div>
</div>
```

**Características**:
- Overlay: `rgba(0, 0, 0, 0.75)`
- Container: `bg-white rounded-lg shadow-2xl`
- Padding: `p-6`
- Título: `text-xl font-semibold`
- Botão fechar: `text-gray-400 hover:text-gray-600`

---

### **5. Espaçamento**

**Entre Seções**:
- `space-y-6` no container principal
- `gap-4` entre cards no mesmo nível

**Padding de Cards**:
- Header: `px-4 py-3`
- Body: `px-4 py-3` ou `p-6`

---

### **6. Ícones**

**Tamanhos**:
- Pequeno: `h-4 w-4` (botões, inline)
- Médio: `h-5 w-5` (cards de métricas)
- Grande: `h-6 w-6` (destaques)

**Prevenção de Corte**:
- Container: `overflow-hidden`
- Ícone: `flex-shrink-0`

---

## 🎨 HIERARQUIA VISUAL

### **Botões**

1. **Primário** (`bg-primary`):
   - Ações principais
   - Cor laranja/brand
   - Sombra: `box-shadow: 0 4px 12px -2px`

2. **Secundário** (`border-input bg-background`):
   - Ações secundárias
   - Borda visível

3. **Terceiro** (ghost/transparente):
   - Ações menos importantes
   - Sem fundo

4. **Danger** (`bg-destructive`):
   - Ações destrutivas
   - Cor vermelha

---

## 📱 RESPONSIVIDADE

### **Breakpoints**
- Mobile: < 768px
- Tablet: 768px - 1023px
- Desktop: 1024px+
- Large Desktop: 1400px+

### **Grids Responsivos**
- Mobile: 1 coluna
- Tablet: 2 colunas
- Desktop: 3-4 colunas (dependendo do conteúdo)

---

## ✅ APLICAÇÃO DOS PADRÕES

### **Páginas Padronizadas**:
1. ✅ WhatsApp - Cards compactados
2. ✅ PDV - Busca e lista otimizadas
3. ✅ Visão Geral - Grid 2 colunas
4. ✅ Cupons - Cards compactados
5. ✅ Cashback - Cards compactados
6. ✅ Modais do PDV - Padronizados

### **Páginas com Padrões Parcialmente Aplicados**:
1. ⚠️ Produtos - Modal precisa padronização
2. ⚠️ Outras páginas - Revisão geral pendente

---

**Status**: Padrões estabelecidos e documentados! Continue aplicando em todas as páginas.

