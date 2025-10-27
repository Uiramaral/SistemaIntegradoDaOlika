<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">Pedido</th>
        <th class="text-left">Cliente</th>
        <th class="text-left">Bairro</th>
        <th class="text-left">Janela</th>
        <th class="text-left">Status</th>
        <th class="text-left">Pagamento</th>
        <th class="text-right">Entrega</th>
        <th class="text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($entregas as $e)
      <tr>
        <td>#{{ $e->id }}</td>
        <td>{{ $e->cliente->nome ?? '-' }}</td>
        <td>{{ $e->bairro ?? $e->cliente->bairro ?? '—' }}</td>
        <td>
          @php $h = optional($e->data_entrega)->format('H:i'); @endphp
          <span class="pill">{{ $h ? ($h.'h') : '—' }}</span>
        </td>
        <td><span class="pill">{{ ucfirst($e->status) }}</span></td>
        <td>
          @if($e->pagamento_na_entrega)
            <span class="pill">Na entrega</span>
          @else
            <span class="pill">Antecipado</span>
          @endif
        </td>
        <td class="text-right">{{ $e->taxa_entrega ? ('R$ '.number_format($e->taxa_entrega,2,',','.')) : '—' }}</td>
        <td class="text-right">
          <a class="pill" href="{{ route('entregas.show',$e) }}">Ver</a>
          <a class="pill" href="{{ route('entregas.edit',$e) }}">Atualizar</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" class="px-4 py-6 text-center text-neutral-500">Sem entregas.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="px-4 py-3">{{ $entregas->withQueryString()->links() }}</div>
