@extends('layouts.dashboard')

@section('title','Templates de Notificação')

@section('page-title','Templates de Notificação')

@section('content')
  <div class="grid gap-4">
    <div class="card p-4">
      <div class="font-semibold mb-2">E-mail (status)</div>
      <iframe srcdoc="@php echo e(view('emails.pedido.status',[ 'pedido'=>$pedidoEx, 'oldStatus'=>'agendado', 'newStatus'=>'producao'])->render()); @endphp" style="width:100%;height:360px;border:0;box-shadow:inset 0 0 0 1px #eee;border-radius:12px;"></iframe>
    </div>

    <div class="card p-4">
      <div class="font-semibold mb-2">WhatsApp (status)</div>
      <pre class="pill" style="white-space:pre-wrap">@include('notifications.whatsapp.status',[ 'pedido'=>$pedidoEx, 'old'=>'agendado', 'new'=>'producao'])</pre>
    </div>
  </div>
@endsection
