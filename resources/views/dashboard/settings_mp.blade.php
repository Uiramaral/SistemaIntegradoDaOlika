@extends('layouts.dashboard')

@section('title','Mercado Pago — Dashboard Olika')

@section('content')

<div class="card">

  <h1 class="text-xl" style="font-weight:800;margin-bottom:10px">Mercado Pago</h1>

  @if(session('ok'))<div class="badge" style="background:#d1fae5;color:#065f46">{{ session('ok') }}</div>@endif

  <form method="POST" action="{{ route('dashboard.mp.save') }}" class="grid gap-3" style="max-width:640px">

    @csrf

    <label>Access Token <input name="mercadopago_access_token" class="card" value="{{ $keys['mercadopago_access_token'] ?? '' }}"></label>

    <label>Public Key <input name="mercadopago_public_key" class="card" value="{{ $keys['mercadopago_public_key'] ?? '' }}"></label>

    <label>Ambiente

      <select name="mercadopago_environment" class="card">

        @php $env = $keys['mercadopago_environment'] ?? 'production'; @endphp

        <option value="production" {{ $env==='production'?'selected':'' }}>production</option>

        <option value="sandbox" {{ $env==='sandbox'?'selected':'' }}>sandbox</option>

      </select>

    </label>

    <label>Webhook URL <input name="mercadopago_webhook_url" class="card" value="{{ $keys['mercadopago_webhook_url'] ?? route('webhook.mercadopago') }}"></label>

    <button class="btn" style="width:max-content">Salvar</button>

  </form>

</div>

@endsection

