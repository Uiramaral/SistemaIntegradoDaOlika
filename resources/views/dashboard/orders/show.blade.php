@extends('layouts.dashboard')

@section('title','Pedido #'.$order->order_number)

@section('content')
<div class="flex items-center justify-between">
  <h1 class="text-2xl font-bold">Pedido #{{ $order->order_number }}</h1>
  <span class="badge">{{ strtoupper($order->status) }}</span>
</div>

<div class="grid md:grid-cols-3 gap-6 mt-6">
  <div class="md:col-span-2 card">
    <h2 class="card-title">Itens</h2>
    <table class="table">
      <thead><tr><th>Produto</th><th class="text-right">Qtd</th><th class="text-right">Total</th></tr></thead>
      <tbody>
        @foreach($order->items as $it)
          <tr>
            <td>{{ $it->custom_name ?? optional($it->product)->name }}</td>
            <td class="text-right">{{ $it->quantity }}</td>
            <td class="text-right">R$ {{ number_format($it->total_price,2,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="mt-4 space-y-1 text-sm">
      <div class="flex justify-between"><span>Subtotal</span><span>R$ {{ number_format($order->total_amount,2,',','.') }}</span></div>
      <div class="flex justify-between"><span>Desconto</span><span>R$ {{ number_format($order->discount_amount,2,',','.') }}</span></div>
      <div class="flex justify-between"><span>Entrega</span><span>R$ {{ number_format($order->delivery_fee,2,',','.') }}</span></div>
      <div class="flex justify-between font-semibold text-lg"><span>Total</span><span>R$ {{ number_format($order->final_amount,2,',','.') }}</span></div>
    </div>
  </div>

  <div class="card">
    <h2 class="card-title">Pagamento</h2>
    <p><b>Método:</b> {{ strtoupper($order->payment_method) }}</p>

    @if($order->payment_method === 'pix' && $order->pix_copia_cola)
      <div class="mt-3">
        <div id="qrcode" class="w-full flex justify-center"></div>
        <textarea id="pixCode" class="input mt-3" rows="3" readonly>{{ $order->pix_copia_cola }}</textarea>
        <button id="copyPix" class="btn w-full mt-2">Copiar código PIX</button>
        @if($order->pix_expires_at)
          <p class="text-xs text-gray-600 mt-2">Válido até {{ \Carbon\Carbon::parse($order->pix_expires_at)->format('d/m/Y H:i') }}</p>
        @endif
      </div>
    @else
      <p class="text-sm text-gray-600 mt-2">Sem PIX para este pedido.</p>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
  const code = document.getElementById('pixCode');
  if(code){
    new QRCode(document.getElementById('qrcode'), { text: code.value, width: 220, height: 220 });
    document.getElementById('copyPix').onclick = async ()=>{
      try{ await navigator.clipboard.writeText(code.value); alert('Código PIX copiado!'); }
      catch(e){ code.select(); document.execCommand('copy'); alert('Código PIX copiado!'); }
    };
  }
})();
</script>
@endpush
