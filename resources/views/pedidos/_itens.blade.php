<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">Produto</th>
        <th class="text-right">Qtd</th>
        <th class="text-right">Preço</th>
        <th class="text-right">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @foreach($pedido->itens as $i)
      <tr>
        <td>{{ $i->produto->nome ?? ('#'.$i->produto_id) }}</td>
        <td class="text-right">{{ $i->quantidade }}</td>
        <td class="text-right">R$ {{ number_format($i->preco_unit,2,',','.') }}</td>
        <td class="text-right">R$ {{ number_format($i->subtotal,2,',','.') }}</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="3" class="text-right font-semibold">Entrega</td>
        <td class="text-right">{{ $pedido->taxa_entrega ? ('R$ '.number_format($pedido->taxa_entrega,2,',','.')) : '—' }}</td>
      </tr>
      <tr>
        <td colspan="3" class="text-right font-semibold">Total</td>
        <td class="text-right">R$ {{ number_format($pedido->total,2,',','.') }}</td>
      </tr>
    </tbody>
  </table>
</div>
