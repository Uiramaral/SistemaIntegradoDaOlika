<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">#</th>
        <th class="text-left">Cliente</th>
        <th class="text-left">Status</th>
        <th class="text-left">Entrega</th>
        <th class="text-right">Itens</th>
        <th class="text-right">Total</th>
        <th class="text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($pedidos as $p)
      <tr>
        <td>#{{ $p->id }}</td>
        <td>{{ $p->cliente->nome ?? '-' }}</td>
        <td><span class="pill">{{ ucfirst($p->status) }}</span></td>
        <td>{{ $p->data_entrega?->format('d/m H:i') ?? '—' }}</td>
        <td class="text-right">{{ $p->itens_count }}</td>
        <td class="text-right">R$ {{ number_format($p->total,2,',','.') }}</td>
        <td class="text-right">
          <a class="pill" href="{{ route('pedidos.show',$p) }}">Ver</a>
          <a class="pill" href="{{ route('pedidos.edit',$p) }}">Editar</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" class="px-4 py-6 text-center text-neutral-500">Sem pedidos.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="px-4 py-3">{{ $pedidos->withQueryString()->links() }}</div>
