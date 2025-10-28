@extends('layouts.dashboard')

@section('title', 'Pedido')

@php
  use Carbon\Carbon;

  $code     = $order->order_number ?? $order->code ?? $order->id;
  $cliente  = $order->customer_name ?? optional($order->customer)->name ?? '—';
  $fone     = $order->customer_phone ?? optional($order->customer)->phone ?? null;
  $email    = $order->customer_email ?? optional($order->customer)->email ?? null;

  $addr = $order->address ?? (object)[
      'street' => $order->street ?? null,
      'number' => $order->number ?? null,
      'district' => $order->district ?? null,
      'city' => $order->city ?? null,
      'state' => $order->state ?? null,
      'zip' => $order->zip ?? null,
      'complement' => $order->complement ?? null,
  ];

  $itens = $order->items ?? $order->order_items ?? collect();
  if (is_array($itens)) { $itens = collect($itens); }

  $subtotal = $order->subtotal ?? ($itens->sum(function($i){ return (float)($i->price ?? $i['price'] ?? 0) * (int)($i->quantity ?? $i['quantity'] ?? 1); }));
  $desconto = $order->discount_amount ?? $order->discount ?? 0;
  $entrega  = $order->delivery_fee ?? 0;
  $total    = $order->final_amount ?? $order->total ?? max(0, $subtotal - $desconto + $entrega);

  $pgtoStatus = $order->payment_status_label ?? $order->payment_status ?? '—';
  $pgtoMethod = $order->payment_method_label ?? $order->payment_method ?? '—';

  $createdAt  = isset($order->created_at) ? Carbon::parse($order->created_at) : null;
  $quando     = $createdAt ? ($createdAt->isToday() ? 'Hoje, '.$createdAt->format('H:i') : $createdAt->format('d/m/Y H:i')) : '—';

  $status = $order->status_label ?? $order->status ?? '—';

  $badge = function($label, $map){
      $key = strtolower((string)$label);
      foreach($map as $k=>$cls){ if(str_contains($key, $k)) return $cls; }
      return 'badge-gray';
  };
  $statusClass = $badge($status, [
    'entregue'=>'badge-green','delivered'=>'badge-green',
    'prepar'=>'badge-orange','prepari'=>'badge-orange',
    'confirm'=>'badge-blue','pago'=>'badge-blue',
    'penden'=>'badge-gray','aguard'=>'badge-gray'
  ]);
  $payClass = $badge($pgtoStatus, [
    'pago'=>'badge-blue','paid'=>'badge-blue',
    'penden'=>'badge-gray','pending'=>'badge-gray'
  ]);

  $brl = fn($v) => 'R$ '.number_format((float)$v,2,',','.');
@endphp

@section('content')

{{-- DEBUG: ORDER-SHOW VIEW ATIVA --}}

<div class="order-page" 
     data-status-url="{{ route('dashboard.orders.status', ['order' => $order->id]) }}">

  {{-- Cabeçalho --}}
  <div class="ord-header">
    <div>
      <h1>Pedido <span class="muted">#{{ $code }}</span></h1>
      <div class="ord-sub">Realizado: {{ $quando }}</div>
    </div>
    <div class="ord-actions">
      <a href="{{ route('dashboard.orders') }}" class="btn btn-soft">← Voltar</a>
      <button id="btn-print" class="btn btn-soft">🖨️ Imprimir</button>
      @if($fone)
        @php
          $wa = preg_replace('/\D/','',$fone);
          $msg = rawurlencode("Olá, sou da Olika. Seguem os detalhes do pedido #{$code} no valor de ".$brl($total).".");
        @endphp
        <a class="btn btn-primary" target="_blank" href="https://wa.me/{{ $wa }}?text={{ $msg }}">WhatsApp</a>
      @endif
    </div>
  </div>

  {{-- Grid principal: itens à esquerda | resumo à direita --}}
  <div class="ord-grid">
    {{-- Coluna esquerda: Itens + Cliente/Endereço --}}
    <div class="col">
      <div class="card">
        <div class="card-head">
          <h3>Itens do Pedido</h3>
          <span class="muted">{{ $itens->count() }} item(ns)</span>
        </div>
        <div class="table-wrapper">
          <table class="table">
            <thead>
              <tr>
                <th>Produto</th>
                <th class="t-center" style="width:110px">Qtd</th>
                <th class="t-right" style="width:140px">Preço</th>
                <th class="t-right" style="width:140px">Total</th>
              </tr>
            </thead>
            <tbody>
              @forelse($itens as $i)
                @php
                  $nome = $i->custom_name ?? $i['name'] ?? ($i->product_name ?? '—');
                  $q    = (int)($i->quantity ?? $i['quantity'] ?? 1);
                  $p    = (float)($i->price ?? $i['price'] ?? 0);
                @endphp
                <tr>
                  <td>{{ $nome }}</td>
                  <td class="t-center">{{ $q }}</td>
                  <td class="t-right">{{ $brl($p) }}</td>
                  <td class="t-right">{{ $brl($p*$q) }}</td>
                </tr>
              @empty
                <tr><td colspan="4">
                  <div class="empty">
                    <div class="empty-ico">🧾</div>
                    <div class="empty-text">Sem itens.</div>
                  </div>
                </td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Cliente & Endereço</h3></div>
        <div class="two-cols">
          <div>
            <div class="label">Cliente</div>
            <div class="value">{{ $cliente }}</div>

            <div class="label">Telefone</div>
            <div class="value">{{ $fone ?? '—' }}</div>

            <div class="label">E-mail</div>
            <div class="value">{{ $email ?? '—' }}</div>
          </div>
          <div>
            <div class="label">Endereço</div>
            <div class="value">
              {{ trim(($addr->street ?? '').', '.($addr->number ?? '')) ?: '—' }}<br>
              {{ ($addr->district ?? '') }} {{ $addr->district && ($addr->city ?? null) ? '•' : '' }} {{ ($addr->city ?? '') }}
              {{ ($addr->state ?? '') ? ' - '.$addr->state : '' }}<br>
              {{ $addr->zip ?? '' }} {{ ($addr->complement ?? '') ? ' • '.$addr->complement : '' }}
            </div>
          </div>
        </div>

        @if(!empty($order->notes))
          <div class="label" style="margin-top:10px">Observações</div>
          <div class="value">{{ $order->notes }}</div>
        @endif
      </div>
    </div>

    {{-- Coluna direita: Resumo/Status/Pagamento --}}
    <aside class="aside">
      <div class="card">
        <div class="card-head"><h3>Resumo</h3></div>
        <div class="totais">
          <div><span>Subtotal</span><span>{{ $brl($subtotal) }}</span></div>
          <div><span>Desconto</span><span>{{ $brl($desconto) }}</span></div>
          <div><span>Entrega</span><span>{{ $brl($entrega) }}</span></div>
          <div class="total"><span>Total</span><span>{{ $brl($total) }}</span></div>
        </div>
      </div>

      <div class="card">
        <div class="card-head"><h3>Status</h3></div>
        <div class="status-row">
          <div>
            <div class="label">Pedido</div>
            <span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
          </div>
          <div>
            <div class="label">Pagamento</div>
            <span class="badge {{ $payClass }}">{{ ucfirst($pgtoStatus) }}</span>
          </div>
        </div>

        {{-- Troca rápida de status --}}
        <form id="form-status" class="status-form" method="POST" action="{{ route('dashboard.orders.status', ['order' => $order->id]) }}">
          @csrf
          <label for="status_select" class="label">Alterar status</label>
          <div class="input-group">
            <select id="status_select" name="status_code" class="input">
              @php
                $opts = [
                  'pending'   => 'Pendente',
                  'confirmed' => 'Confirmado',
                  'preparing' => 'Em preparo',
                  'delivered' => 'Entregue',
                  'canceled'  => 'Cancelado',
                ];
                $currentKey = strtolower($order->status ?? '');
              @endphp
              @foreach($opts as $val => $label)
                <option value="{{ $val }}" {{ $currentKey === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Atualizar</button>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-head"><h3>Pagamento</h3></div>
        <div class="meta">
          <div class="row"><span>Método</span><span>{{ ucfirst($pgtoMethod) }}</span></div>
          <div class="row"><span>Status</span><span>{{ ucfirst($pgtoStatus) }}</span></div>
        </div>

        {{-- PIX (se houver) --}}
        @php
          $pix_qr = $order->pix_qr_base64 ?? $order->pix_qr ?? null;
          $pix_code = $order->pix_copy_paste ?? $order->pix_code ?? null;
        @endphp
        @if($pix_qr || $pix_code)
          <div class="pix-box">
            @if($pix_code)
              <div class="pix-line">
                <input id="pix-code" class="input" readonly value="{{ $pix_code }}">
                <button id="btn-copy" class="btn btn-soft">Copiar</button>
              </div>
            @endif
          </div>
        @endif
      </div>
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const btnPrint = document.getElementById('btn-print');
  btnPrint && (btnPrint.onclick = ()=> window.print());

  const copy = (t) => navigator.clipboard?.writeText(t).then(()=>alert('Copiado!'));
  const btnCopy = document.getElementById('btn-copy');
  const pixCode = document.getElementById('pix-code');
  if(btnCopy && pixCode){ btnCopy.onclick = ()=> copy(pixCode.value); }

  // confirmação leve na troca de status
  const form = document.getElementById('form-status');
  form && form.addEventListener('submit', (e)=>{
    const sel = document.getElementById('status_select');
    if(!confirm('Atualizar status para "'+ (sel.options[sel.selectedIndex]?.text || sel.value) +'"?')) e.preventDefault();
  });
})();
</script>
@endpush

