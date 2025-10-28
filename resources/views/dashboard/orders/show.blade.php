@extends('layouts.app')
@section('content')
<div class="ol-card">
  <div class="ol-card__title">Pedido #{{ $order->order_number }}</div>

  <form action="{{ route('dashboard.orders.update',$order->id) }}" method="post" class="ol-grid ol-grid--4">
    @csrf
    <div>
      <label class="ol-label">Status</label>
      <select name="status" class="ol-select">
        @foreach(['pending'=>'Pendente','confirmed'=>'Confirmado','preparing'=>'Preparando','delivered'=>'Entregue'] as $k=>$v)
          <option value="{{ $k }}" @selected($order->status==$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="ol-label">Pagamento</label>
      <select name="payment_status" class="ol-select">
        @foreach(['pending'=>'Pendente','paid'=>'Pago','refunded'=>'Estornado'] as $k=>$v)
          <option value="{{ $k }}" @selected($order->payment_status==$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="ol-label">Data entrega</label>
      <input type="date" name="delivery_date" class="ol-input" value="{{ $order->delivery_date }}">
    </div>
    <div>
      <label class="ol-label">Hora entrega</label>
      <input type="time" name="delivery_time" class="ol-input" value="{{ $order->delivery_time }}">
    </div>
    <div class="ol-grid--4" style="grid-column:1/-1">
      <label class="ol-label">Observações</label>
      <textarea name="note" class="ol-textarea">{{ $order->note }}</textarea>
    </div>
    <div style="grid-column:1/-1; display:flex; justify-content:flex-end">
      <button class="ol-cta">Salvar alterações</button>
    </div>
  </form>

  <hr class="mt-4">
  <div class="ol-card__title">Itens</div>
  <table class="ol-table" id="order-items">
    <thead><tr><th>Produto</th><th>Preço</th><th>Qtd</th><th>Total</th><th></th></tr></thead>
    <tbody>
      @foreach($items as $it)
        <tr>
          <td>{{ $it->product_name ?? 'Item' }}</td>
          <td>R$ {{ number_format($it->unit_price,2,',','.') }}</td>
          <td>{{ $it->quantity }}</td>
          <td>R$ {{ number_format($it->total_price,2,',','.') }}</td>
          <td>
            <form method="post" action="{{ route('dashboard.orders.items.remove',[$order->id,$it->id]) }}">
              @csrf @method('delete')
              <button class="ol-btn">Remover</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <form class="ol-grid ol-grid--4 mt-3" method="post" action="{{ route('dashboard.orders.items.add',$order->id) }}">
    @csrf
    <input name="name" class="ol-input" placeholder="Item avulso (desc)" required>
    <input name="price" type="number" step="0.01" class="ol-input" placeholder="Preço" required>
    <input name="qty" type="number" min="1" class="ol-input" value="1" placeholder="Qtd" required>
    <button class="ol-btn">Adicionar item</button>
  </form>
</div>
@endsection

@push('page-scripts')
<script src="{{ asset('js/orders-show.js') }}"></script>
@endpush