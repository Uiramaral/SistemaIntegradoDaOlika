@extends('layouts.app')

@section('content')
  <section class="olika-hero"></section>

  <main class="olika-container" style="margin-top:-72px;">
    <div style="display:flex; align-items:flex-end; gap:1rem; margin-bottom:1rem">
      <img src="{{ asset('images/logo-olika.png') }}" alt="Olika" style="width:84px;height:84px;border-radius:999px;border:4px solid #fff;box-shadow:0 8px 24px rgba(0,0,0,.12)">
      <div style="display:flex;flex-direction:column;gap:.25rem">
        <h1 style="font-weight:800; font-size:1.75rem; line-height:1">Olika</h1>
        <div class="muted" style="font-weight:600">PÃ£es Artesanais</div>
        <div><span class="badge-open">Aberto Agora</span></div>
      </div>
    </div>
  </main>
@endsection
