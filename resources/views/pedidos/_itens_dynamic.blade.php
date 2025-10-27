@props(['produtos' => collect(), 'pedido' => null])

@php
  $initialItems = $pedido?->itens->map(fn($i)=>[
    'produto_id' => $i->produto_id,
    'quantidade' => $i->quantidade,
    'preco_unit' => $i->preco_unit,
  ])->values()->toArray() ?? [];
@endphp

<div x-data="pedidoItems({ produtos: @js($produtos->map(fn($p)=>['id'=>$p->id,'nome'=>$p->nome,'preco'=>$p->preco])), initialItems: @js($initialItems), taxaEntrega: @js(old('taxa_entrega', $pedido->taxa_entrega ?? 0)), desconto: @js(old('desconto', $pedido->desconto ?? 0)), cupomCodigo: @js(old('cupom_codigo', '')) })" class="grid gap-4">
  <div class="overflow-auto">
    <table class="table-compact w-full">
      <thead>
        <tr>
          <th class="text-left">Produto</th>
          <th class="text-right">Qtd</th>
          <th class="text-right">Preço</th>
          <th class="text-right">Subtotal</th>
          <th class="text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <template x-for="(i,idx) in items" :key="idx">
          <tr>
            <td>
              <select class="pill" :name="`itens[${idx}][produto_id]`" x-model="i.produto_id" @change="onProdutoChange(idx,$event)">
                <option value="">Produto…</option>
                <template x-for="p in produtos" :key="p.id">
                  <option :value="p.id" :data-preco="p.preco" x-text="p.nome"></option>
                </template>
              </select>
            </td>
            <td class="text-right"><input class="pill" type="number" min="1" x-model.number="i.quantidade" :name="`itens[${idx}][quantidade]`" /></td>
            <td class="text-right"><input class="pill" type="number" step="0.01" min="0" x-model.number="i.preco_unit" :name="`itens[${idx}][preco_unit]`" /></td>
            <td class="text-right">
              <span class="pill" x-text="`R$ ${linhaSubtotal(i).toLocaleString('pt-BR',{minimumFractionDigits:2})}`"></span>
            </td>
            <td class="text-right">
              <button type="button" class="pill" @click="remove(idx)">Remover</button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
  
  <div class="flex gap-2">
    <button type="button" class="pill" @click="add()">+ Adicionar item</button>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="grid gap-2">
      <label>Taxa de entrega</label>
      <input type="number" step="0.01" min="0" name="taxa_entrega" class="pill" x-model.number="taxaEntrega" />
    </div>
    <div class="grid gap-2">
      <label>Desconto (R$)</label>
      <input type="number" step="0.01" min="0" name="desconto" class="pill" x-model.number="desconto" />
    </div>
    <div class="grid gap-2">
      <label>Cupom</label>
      <div class="flex gap-2">
        <input name="cupom_codigo" class="pill" placeholder="CÓDIGO" x-model="cupomCodigo" />
        <button type="button" class="pill" @click="aplicarCupom()">Aplicar</button>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
    <div class="stat">
      <div class="label">Subtotal itens</div>
      <div class="value" x-text="`R$ ${itensTotal().toLocaleString('pt-BR',{minimumFractionDigits:2})}`"></div>
    </div>
    <div class="stat">
      <div class="label">Cupom</div>
      <div class="value" x-text="cupomValor ? `- R$ ${cupomValor.toLocaleString('pt-BR',{minimumFractionDigits:2})}` : '—'"></div>
    </div>
    <div class="stat">
      <div class="label">Total geral</div>
      <div class="value" x-text="`R$ ${totalGeral().toLocaleString('pt-BR',{minimumFractionDigits:2})}`"></div>
    </div>
  </div>
</div>
