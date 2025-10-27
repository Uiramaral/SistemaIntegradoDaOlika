<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">Cliente</th>
        <th class="text-left">Destinatário</th>
        <th class="text-left">Endereço</th>
        <th class="text-left">Bairro/Cidade</th>
        <th class="text-left">CEP</th>
        <th class="text-left">Status</th>
        <th class="text-right">Taxa base</th>
        <th class="text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($enderecos as $e)
      <tr>
        <td>{{ $e->cliente->nome ?? ('#'.$e->cliente_id) }}</td>
        <td>{{ $e->nome_destinatario }}</td>
        <td>{{ $e->endereco }} {{ $e->numero ? ', '.$e->numero : '' }} {{ $e->complemento ? ' — '.$e->complemento : '' }}</td>
        <td>{{ $e->bairro }} — {{ $e->cidade }}/{{ $e->uf }}</td>
        <td>{{ $e->cep }}</td>
        <td><span class="pill">{{ $e->padrao ? 'Padrão' : '—' }}</span></td>
        <td class="text-right">{{ $e->taxa_base ? ('R$ '.number_format($e->taxa_base,2,',','.')) : '—' }}</td>
        <td class="text-right">
          <a href="{{ route('enderecos.show',$e) }}" class="pill">Ver</a>
          <a href="{{ route('enderecos.edit',$e) }}" class="pill">Editar</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" class="px-4 py-6 text-center text-neutral-500">Nenhum endereço.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="px-4 py-3">{{ $enderecos->withQueryString()->links() }}</div>
