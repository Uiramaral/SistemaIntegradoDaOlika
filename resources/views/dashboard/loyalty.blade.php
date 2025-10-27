@extends('layouts.dashboard')

@section('title','Fidelidade — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Programa de Fidelidade</h1>

  <table>

    <thead><tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Status</th></tr></thead>

    <tbody>

      @foreach($rows as $r)

        <tr>

          <td>#{{ $r->id }}</td>

          <td>{{ $r->name }}</td>

          <td>{{ $r->description ?? '—' }}</td>

          <td><span class="badge">{{ $r->is_active ? 'Ativo' : 'Inativo' }}</span></td>

        </tr>

      @endforeach

    </tbody>

  </table>

  <div style="margin-top:10px">{{ $rows->links() }}</div>

</div>

@endsection

