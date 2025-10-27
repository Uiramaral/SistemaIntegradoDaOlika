@extends('layouts.dashboard')

@section('title','Log #'.$log->id)

@section('page-title','Log de Auditoria')

@section('page-subtitle', class_basename($log->model_type).' #'.$log->model_id.' — '.$log->action)

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">Metadados</div>
      <div class="text-sm text-neutral-500">Usuário: {{ $log->user->name ?? '—' }} (ID: {{ $log->user_id ?? '—' }})</div>
      <div class="text-sm text-neutral-500">IP: {{ $log->ip }} — UA: {{ $log->ua }}</div>
      <div class="text-sm text-neutral-500">Quando: {{ $log->created_at->format('d/m/Y H:i:s') }}</div>
    </div>

    <div class="card overflow-hidden">
      <div class="px-4 py-3 border-b font-semibold">Diferenças (old → new)</div>
      <div class="overflow-auto">
        <table class="table-compact">
          <thead>
            <tr>
              <th class="text-left">Campo</th>
              <th class="text-left">De</th>
              <th class="text-left">Para</th>
            </tr>
          </thead>
          <tbody>
            @php($old = $log->changes['old'] ?? [])
            @php($new = $log->changes['new'] ?? [])
            @foreach(array_unique(array_merge(array_keys($old), array_keys($new))) as $k)
              @continue(in_array($k, ['updated_at','created_at','password','remember_token']))
              <tr>
                <td>{{ $k }}</td>
                <td class="text-neutral-500">{{ is_scalar($old[$k] ?? null) ? ($old[$k] ?? '—') : json_encode($old[$k] ?? null) }}</td>
                <td>{{ is_scalar($new[$k] ?? null) ? ($new[$k] ?? '—') : json_encode($new[$k] ?? null) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
