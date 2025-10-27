@php($p = $pedido)

<!doctype html>
<html lang="pt-BR">
  <body style="font-family:system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, sans-serif; color:#111;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
      <h2>Olá, {{ $p->cliente->nome ?? 'cliente' }} 👋</h2>
      <p>Seu pedido <strong>#{{ $p->id }}</strong> mudou de status: <strong>{{ ucfirst($oldStatus) }}</strong> → <strong>{{ ucfirst($newStatus) }}</strong>.</p>
      @if($p->data_entrega)
        <p>Previsão/Janela de entrega: <strong>{{ $p->data_entrega->format('d/m/Y H:i') }}</strong>.</p>
      @endif
      <p>Total: <strong>R$ {{ number_format($p->total,2,',','.') }}</strong></p>
      <p style="font-size:12px;color:#6b7280;">Se você não reconhece esta atualização, responda este e-mail.</p>
      <hr style="border:none;border-top:1px solid #eee;margin:16px 0;" />
      <p style="font-size:12px;color:#6b7280;">Equipe Olika</p>
    </div>
  </body>
</html>
