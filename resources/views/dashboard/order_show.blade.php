{{-- PÁGINA: Detalhes do Pedido (Visualização Individual) --}}
@extends('layouts.dashboard')

@section('title', 'Pedido #'.$order->number.' — Dashboard Olika')

@section('content')
<div class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Pedido #{{ $order->order_number ?? $order->number }}</h1>

  @if(session('ok'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('ok') }}</div>
  @endif

  <div class="bg-white shadow rounded p-4 mb-6">
    <h2 class="font-semibold mb-2">Informações do Pedido</h2>
    <p><strong>Cliente:</strong> {{ $order->customer->name ?? '—' }}</p>
    <p><strong>Telefone:</strong> {{ $order->customer->phone ?? '—' }}</p>
    <p><strong>Total:</strong> R$ {{ number_format($order->final_amount ?? $order->total_amount ?? 0, 2, ',', '.') }}</p>
    <p><strong>Forma de Pagamento:</strong> {{ strtoupper($order->payment_method ?? 'N/A') }}</p>
    <p><strong>Status Atual:</strong> <span class="font-semibold">{{ $order->status }}</span></p>
  </div>

  {{-- Atualizar status --}}
  <div class="bg-white shadow rounded p-4 mb-6">
    <h2 class="font-semibold mb-3">Alterar Status</h2>
    <form method="POST" action="{{ route('dashboard.orders.status', $order) }}" class="grid md:grid-cols-2 gap-3">
      @csrf
      <select name="status_code" class="border p-2 rounded" required>
        @foreach(\Illuminate\Support\Facades\DB::table('order_statuses')->where('active',1)->orderBy('id')->get() as $s)
          <option value="{{ $s->code }}" {{ $order->status===$s->code?'selected':'' }}>
            {{ $s->name }}
            {{ $s->notify_customer?'• Cliente':'' }}
            {{ $s->notify_admin?'• Admin':'' }}
          </option>
        @endforeach
      </select>
      <input name="note" placeholder="Observação (opcional)" class="border p-2 rounded">
      <button class="bg-amber-600 hover:bg-amber-700 text-white py-2 rounded md:col-span-2">Atualizar Status</button>
    </form>
  </div>

  {{-- Histórico --}}
  <div class="bg-white shadow rounded p-4">
    <h2 class="font-semibold mb-2">Histórico de Status</h2>
    <table class="w-full text-sm border">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="p-2">Data</th>
          <th class="p-2">De</th>
          <th class="p-2">Para</th>
          <th class="p-2">Nota</th>
        </tr>
      </thead>
      <tbody>
        @foreach(\Illuminate\Support\Facades\DB::table('order_status_history')->where('order_id',$order->id)->orderByDesc('id')->get() as $h)
          <tr class="border-t">
            <td class="p-2">{{ \Carbon\Carbon::parse($h->created_at)->format('d/m H:i') }}</td>
            <td class="p-2">{{ $h->old_status ?? '—' }}</td>
            <td class="p-2 font-semibold">{{ $h->new_status }}</td>
            <td class="p-2">{{ $h->note }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Itens do Pedido --}}
  <div class="bg-white shadow rounded p-4 mt-6">
    <h2 class="font-semibold mb-2">Itens do Pedido</h2>
    <table class="w-full text-sm border">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="p-2">Produto</th>
          <th class="p-2 text-center">Qtd</th>
          <th class="p-2 text-right">Preço</th>
          <th class="p-2 text-right">Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
          <tr class="border-t">
            <td class="p-2">{{ $item->product_name }}</td>
            <td class="p-2 text-center">{{ $item->quantity }}</td>
            <td class="p-2 text-right">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
            <td class="p-2 text-right">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

