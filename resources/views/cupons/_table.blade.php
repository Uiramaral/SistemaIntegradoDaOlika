@php
  function cupom_status_badge($c){
    $now = now();
    if(!$c->ativo){ return 'Inativo'; }
    if($c->validade_inicio && $c->validade_inicio->isFuture()){ return 'Futuro'; }
    if($c->validade_fim && $c->validade_fim->isPast()){ return 'Expirado'; }
    return 'Válido';
  }
@endphp

<div class="overflow-auto">
  <table class="table-compact">
    <thead>
      <tr>
        <th class="text-left">Código</th>
        <th class="text-left">Descrição</th>
        <th class="text-left">Tipo</th>
        <th class="text-left">Valor</th>
        <th class="text-left">Validade</th>
        <th class="text-left">Status</th>
        <th class="text-right">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($cupons as $c)
      <tr>
        <td>{{ $c->codigo }}</td>
        <td>{{ $c->descricao }}</td>
        <td><span class="pill">{{ $c->tipo === 'percent' ? 'Percentual' : 'Valor fixo' }}</span></td>
        <td>
          @if($c->tipo === 'percent')
            {{ number_format($c->valor,0) }}%
          @else
            R$ {{ number_format($c->valor,2,',','.') }}
          @endif
        </td>
        <td>
          @if($c->validade_inicio || $c->validade_fim)
            {{ $c->validade_inicio?->format('d/m/Y') ?? '—' }} — {{ $c->validade_fim?->format('d/m/Y') ?? '—' }}
          @else
            —
          @endif
        </td>
        <td><span class="pill">{{ cupom_status_badge($c) }}</span></td>
        <td class="text-right">
          <a href="{{ route('cupons.show',$c) }}" class="pill">Ver</a>
          <a href="{{ route('cupons.edit',$c) }}" class="pill">Editar</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" class="px-4 py-6 text-center text-neutral-500">Nenhum cupom.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div class="px-4 py-3">{{ $cupons->withQueryString()->links() }}</div>
