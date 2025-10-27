@extends('layouts.dashboard')

@section('title','Status & Templates')

@section('page-title','Status & Templates')

@section('page-subtitle','Gerencie os status dos pedidos e templates de mensagens')

@section('content')
<div class="grid-2">
  <!-- Coluna esquerda: lista de status -->
  <section class="card">
    <h2 class="card-title">Status dos Pedidos</h2>
    <p class="card-sub">Personalize os status disponíveis para seus pedidos</p>

    <ul class="status-list">
      <li class="status-item">
        <span class="dot dot-red"></span>
        <div class="status-texts">
          <div class="status-name">Pendente</div>
          <div class="status-desc">Aguardando confirmação</div>
        </div>
        <button class="gear" aria-label="Configurar">⚙️</button>
      </li>

      <li class="status-item">
        <span class="dot dot-blue"></span>
        <div class="status-texts">
          <div class="status-name">Confirmado</div>
          <div class="status-desc">Pedido confirmado</div>
        </div>
        <button class="gear">⚙️</button>
      </li>

      <li class="status-item">
        <span class="dot dot-orange"></span>
        <div class="status-texts">
          <div class="status-name">Em Preparo</div>
          <div class="status-desc">Sendo preparado</div>
        </div>
        <button class="gear">⚙️</button>
      </li>

      <li class="status-item">
        <span class="dot dot-purple"></span>
        <div class="status-texts">
          <div class="status-name">Saiu para Entrega</div>
          <div class="status-desc">Em rota de entrega</div>
        </div>
        <button class="gear">⚙️</button>
      </li>

      <li class="status-item">
        <span class="dot dot-green"></span>
        <div class="status-texts">
          <div class="status-name">Entregue</div>
          <div class="status-desc">Pedido entregue</div>
        </div>
        <button class="gear">⚙️</button>
      </li>

      <li class="status-item">
        <span class="dot dot-gray"></span>
        <div class="status-texts">
          <div class="status-name">Cancelado</div>
          <div class="status-desc">Pedido cancelado</div>
        </div>
        <button class="gear">⚙️</button>
      </li>
    </ul>
  </section>

  <!-- Coluna direita: formulário + preview -->
  <section class="card">
    <h2 class="card-title">Configurar Template</h2>
    <p class="card-sub">Personalize mensagens para cada status</p>

    <form class="form">
      <label class="lbl">Selecionar Status</label>
      <select class="inp">
        <option>Confirmado</option>
        <option>Pendente</option>
        <option>Em Preparo</option>
        <option>Saiu para Entrega</option>
        <option>Entregue</option>
        <option>Cancelado</option>
      </select>

      <label class="lbl">Título da Notificação</label>
      <input class="inp" placeholder="Pedido Confirmado!">

      <label class="lbl">Mensagem</label>
      <textarea class="inp" rows="4" placeholder="Seu pedido #{{ '{numero}' }} foi confirmado e está sendo preparado com todo carinho! 🥐"></textarea>

      <div class="hint">Variáveis disponíveis: <code>{{ '{numero}' }}</code>, <code>{{ '{cliente}' }}</code>, <code>{{ '{valor}' }}</code></div>

      <label class="lbl">Cor do Status</label>
      <div class="color-row">
        <button type="button" class="c c-red"></button>
        <button type="button" class="c c-orange"></button>
        <button type="button" class="c c-yellow"></button>
        <button type="button" class="c c-green"></button>
        <button type="button" class="c c-teal"></button>
        <button type="button" class="c c-blue"></button>
        <button type="button" class="c c-purple"></button>
      </div>

      <div class="form-actions">
        <button class="btn primary">Salvar Template</button>
      </div>
    </form>
  </section>

  <!-- Preview (quebra para baixo no mobile) -->
  <section class="card span-2">
    <h3 class="card-title">Prévia da Notificação</h3>
    <div class="notif">
      <div class="notif-line">
        <span class="dot dot-orange"></span>
        <span class="badge">Pedido #123</span>
      </div>
      <div class="notif-title">Pedido Confirmado!</div>
      <div class="notif-text">Seu pedido #123 foi confirmado e está sendo preparado com todo carinho! 🥐</div>
      <div class="notif-time">Há 2 minutos</div>
    </div>
  </section>
</div>
@endsection
