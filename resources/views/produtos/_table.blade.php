<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">Nome</th>
        <th class="text-left">SKU</th>
        <th class="text-left">Preço</th>
        <th class="text-left">Status</th>
        <th class="text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($produtos as $p)
      <tr>
        <td>{{ $p->nome }}</td>
        <td>{{ $p->sku }}</td>
        <td>R$ {{ number_format($p->preco,2,',','.') }}</td>
        <td><span class="pill">{{ $p->ativo ? 'Ativo' : 'Inativo' }}</span></td>
        <td class="text-right">
          <a href="{{ route('produtos.show',$p) }}" class="pill">Ver</a>
          <a href="{{ route('produtos.edit',$p) }}" class="pill">Editar</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="5" class="px-4 py-6 text-center text-neutral-500">Nenhum produto.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="px-4 py-3">{{ $produtos->withQueryString()->links() }}</div>
