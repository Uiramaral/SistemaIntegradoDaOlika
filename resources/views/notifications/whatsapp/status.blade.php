@php($p = $pedido)

*Olika* ✅
Pedido #{{ $p->id }} atualizado: *{{ ucfirst($old) }} → {{ ucfirst($new) }}*
Total: R$ {{ number_format($p->total,2,',','.') }}@if($p->data_entrega)

Entrega: {{ $p->data_entrega->format('d/m H:i') }}@endif

Obrigado! 🙌
