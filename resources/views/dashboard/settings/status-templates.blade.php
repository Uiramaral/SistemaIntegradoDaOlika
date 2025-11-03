@extends('dashboard.layouts.app')

@section('title', 'Status & Templates')
@section('page_title', 'Status & Templates')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Status & Templates</h1>
      <p class="text-muted-foreground">Gerencie os status dos pedidos e templates de mensagens</p>
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Status dos Pedidos</h3>
        <p class="text-sm text-muted-foreground">Personalize os status disponíveis para seus pedidos</p>
      </div>
      <div class="p-6 pt-0 grid gap-3">
        @forelse($statuses as $st)
          <form action="{{ route('dashboard.status-templates.status.update', $st->id) }}" method="POST" class="rounded-md border p-3 space-y-3">
            @csrf
            <div class="grid md:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-sm font-medium">Código</label>
                <input value="{{ $st->code }}" class="flex h-10 w-full rounded-md border bg-muted px-3 py-2 text-sm" disabled>
              </div>
              <div class="space-y-1">
                <label class="text-sm font-medium">Nome</label>
                <input name="name" value="{{ old('name', $st->name) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
              </div>
            </div>
            <div class="grid md:grid-cols-3 gap-3">
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_final" value="1" {{ $st->is_final ? 'checked' : '' }}> Finaliza pedido</label>
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="notify_customer" value="1" {{ $st->notify_customer ? 'checked' : '' }}> Notificar cliente</label>
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="notify_admin" value="1" {{ $st->notify_admin ? 'checked' : '' }}> Notificar admin</label>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-sm font-medium">Template de WhatsApp</label>
                <select name="whatsapp_template_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                  <option value="">— Sem template —</option>
                  @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}" {{ (int)($st->whatsapp_template_id ?? 0) === (int)$tpl->id ? 'selected' : '' }}>{{ $tpl->slug }}</option>
                  @endforeach
                </select>
              </div>
              <div class="flex items-end justify-between gap-3">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="active" value="1" {{ $st->active ? 'checked' : '' }}> Ativo</label>
                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">Salvar</button>
              </div>
            </div>
          </form>
        @empty
          <div class="rounded-md border p-4 text-sm text-muted-foreground">Nenhum status cadastrado.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-semibold leading-none tracking-tight">Templates de WhatsApp</h3>
        <p class="text-sm text-muted-foreground">Crie e edite templates utilizados nos status</p>
      </div>
      <div class="p-6 pt-0 space-y-6">
        <form action="{{ route('dashboard.status-templates.template.save') }}" method="POST" class="space-y-4">
          @csrf
          <input type="hidden" name="id" id="tpl_id" value="">
          <div class="grid md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="text-sm font-medium">Slug</label>
              <input name="slug" id="tpl_slug" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="ex.: order_confirmed">
            </div>
            <div class="flex items-end">
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="active" id="tpl_active" value="1" checked> Ativo</label>
            </div>
          </div>
          <div class="space-y-1">
            <label class="text-sm font-medium">Conteúdo</label>
            <textarea name="content" id="tpl_content" rows="6" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="Mensagem com variáveis como {nome}, {pedido}, {total}"></textarea>
            <p class="text-xs text-muted-foreground">Variáveis suportadas: {nome}, {pedido}, {total}, {status}, {link}</p>
          </div>
          <div class="flex items-center justify-end gap-3">
            <button type="reset" class="rounded-md border px-4 h-9 text-sm">Limpar</button>
            <button class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4">Salvar Template</button>
          </div>
        </form>

        <div class="rounded-md border">
          <div class="p-3 border-b text-sm font-medium">Templates Existentes</div>
          <div class="divide-y">
            @forelse($templates as $tpl)
              <div class="p-3 flex items-start justify-between gap-4">
                <div class="text-sm">
                  <div class="font-semibold">{{ $tpl->slug }} {!! $tpl->active ? '<span class="ml-2 text-xs text-green-600">ativo</span>' : '<span class="ml-2 text-xs text-gray-400">inativo</span>' !!}</div>
                  <div class="text-muted-foreground whitespace-pre-line mt-1">{{ Str::limit($tpl->content, 160) }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <button type="button" class="rounded-md border px-3 py-1 text-xs" onclick="editTemplate({{ $tpl->id }})">Editar</button>
                  <form action="{{ route('dashboard.status-templates.template.delete', $tpl->id) }}" method="POST" onsubmit="return confirm('Excluir este template?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-md border px-3 py-1 text-xs text-red-600">Excluir</button>
                  </form>
                </div>
              </div>
            @empty
              <div class="p-4 text-sm text-muted-foreground">Nenhum template cadastrado.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6">
      <h3 class="text-2xl font-semibold leading-none tracking-tight">Prévia Rápida</h3>
    </div>
    <div class="p-6 pt-0">
      <div class="rounded-lg border p-4 space-y-1 text-sm" id="previewBox">
        <div class="text-muted-foreground">Pedido #{pedido}</div>
        <div class="font-semibold" id="previewTitle">Mensagem</div>
        <div id="previewContent" class="whitespace-pre-wrap text-muted-foreground">Selecione um template ou edite o conteúdo acima.</div>
      </div>
    </div>
  </div>
</div>
@push('scripts')
<script>
async function editTemplate(id){
  try{
    const res = await fetch('{{ url('/dashboard/settings/status-templates/template') }}/'+id);
    const data = await res.json();
    if(data && !data.error){
      document.getElementById('tpl_id').value = data.id;
      document.getElementById('tpl_slug').value = data.slug || '';
      document.getElementById('tpl_content').value = data.content || '';
      const active = document.getElementById('tpl_active');
      if(active){ active.checked = !!(data.active); }
      updatePreview();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }catch(e){ console.error(e); }
}

function updatePreview(){
  const content = document.getElementById('tpl_content')?.value || '';
  const preview = content
    .replaceAll('{nome}','Maria')
    .replaceAll('{pedido}','123')
    .replaceAll('{total}','R$ 99,90')
    .replaceAll('{status}','Confirmado')
    .replaceAll('{link}','https://wa.me/');
  document.getElementById('previewContent').textContent = preview;
}

document.addEventListener('DOMContentLoaded', function(){
  const content = document.getElementById('tpl_content');
  if(content){ content.addEventListener('input', updatePreview); }
  updatePreview();
});
</script>
@endpush
@endsection



