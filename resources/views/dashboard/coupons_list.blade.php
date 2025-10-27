@extends('layouts.dashboard')

@section('title','Cupons — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Cupons</h1>

  <table>

    <thead><tr><th>Código</th><th>Nome</th><th>Tipo</th><th>Valor</th><th>Usos</th><th>Válido</th><th>Status</th></tr></thead>

    <tbody>

      @foreach($coupons as $c)

        <tr>

          <td><strong>{{ $c->code }}</strong></td>

          <td>{{ $c->name }}</td>

          <td>{{ $c->type }}</td>

          <td>{{ $c->formatted_value }}</td>

          <td>{{ $c->used_count }} / {{ $c->usage_limit ?? '∞' }}</td>

          <td>{{ \Carbon\Carbon::parse($c->expires_at)->format('d/m/Y') ?? '—' }}</td>

          <td><span class="badge">{{ $c->is_active ? 'Ativo' : 'Inativo' }}</span></td>

        </tr>

      @endforeach

    </tbody>

  </table>

  <div style="margin-top:10px">{{ $coupons->links() }}</div>

</div>

@endsection

