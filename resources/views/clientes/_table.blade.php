<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">Nome</th>
        <th class="text-left">Telefone</th>
        <th class="text-left">E-mail</th>
        <th class="text-left">Endereço</th>
        <th class="text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($clientes as $c)
      <tr>
        <td>{{ $c->nome }}</td>
        <td>{{ $c->telefone }}</td>
        <td>{{ $c->email }}</td>
        <td>{{ $c->endereco }}</td>
        <td class="text-right">
          <a href="{{ route('clientes.show',$c) }}" class="pill">Ver</a>
          <a href="{{ route('clientes.edit',$c) }}" class="pill">Editar</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="5" class="px-4 py-6 text-center text-neutral-500">Nenhum cliente.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="px-4 py-3">{{ $clientes->withQueryString()->links() }}</div>
