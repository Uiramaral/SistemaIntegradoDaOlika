@extends('dashboard.layouts.app')

@section('title', 'Status e Templates')
@section('page_title', 'Status e Templates')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">

  <div class="grid gap-6">
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
      <div class="flex flex-col space-y-1.5 p-6 border-b">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-2xl font-semibold leading-none tracking-tight">Configuração de Status dos Pedidos</h3>
            <p class="text-sm text-muted-foreground mt-1">Configure os status disponíveis e associe templates de notificação WhatsApp</p>
          </div>
          <button onclick="openTemplateModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium transition-all">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Novo Template
          </button>
        </div>
      </div>
      <div class="p-6 grid gap-4">
        @forelse($statuses as $st)
          <form action="{{ route('dashboard.settings.status-templates.status.update', $st->id) }}" method="POST" class="rounded-lg border p-4 space-y-4 hover:shadow-md transition-shadow bg-gradient-to-br from-white to-gray-50">
            @csrf
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 grid md:grid-cols-3 gap-4">
                <div class="space-y-2">
                  <label class="text-sm font-medium text-gray-700">Identificador</label>
                  <div class="flex items-center gap-2">
                    <input value="{{ $st->code }}" class="flex h-10 w-full rounded-md border bg-gray-100 px-3 py-2 text-sm font-mono font-semibold" disabled>
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $st->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                      {{ $st->active ? 'Ativo' : 'Inativo' }}
                    </span>
                  </div>
                </div>
                <div class="space-y-2 md:col-span-2">
                  <label class="text-sm font-medium text-gray-700">Nome Exibido</label>
                  <input name="name" value="{{ old('name', $st->name) }}" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20" placeholder="Ex: Pedido Confirmado">
                </div>
              </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700 flex items-center gap-2">
                  <i data-lucide="message-square" class="h-4 w-4"></i>
                  Template de Notificação
                </label>
                <select name="whatsapp_template_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20" onchange="previewTemplateInStatus(this)">
                  <option value="">— Nenhum template associado —</option>
                  @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}" {{ (int)($st->whatsapp_template_id ?? 0) === (int)$tpl->id ? 'selected' : '' }} data-content="{{ $tpl->content }}">
                      {{ $tpl->slug }} {{ $tpl->active ? '✓' : '(inativo)' }}
                    </option>
                  @endforeach
                </select>
              </div>
              
              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">Configurações de Comportamento</label>
                <div class="flex flex-wrap gap-3">
                  <label class="inline-flex items-center gap-2 px-3 py-2 rounded-md border cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="is_final" value="1" {{ $st->is_final ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                    <span class="text-sm">Finaliza pedido</span>
                  </label>
                  <label class="inline-flex items-center gap-2 px-3 py-2 rounded-md border cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="notify_customer" value="1" {{ $st->notify_customer ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                    <span class="text-sm">Notificar cliente</span>
                  </label>
                  <label class="inline-flex items-center gap-2 px-3 py-2 rounded-md border cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="notify_admin" value="1" {{ $st->notify_admin ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                    <span class="text-sm">Notificar admin</span>
                  </label>
                  <label class="inline-flex items-center gap-2 px-3 py-2 rounded-md border cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="active" value="1" {{ $st->active ? 'checked' : '' }} class="rounded text-primary focus:ring-primary">
                    <span class="text-sm">Status ativo</span>
                  </label>
                </div>
              </div>
            </div>
            
            <div class="flex items-center justify-end pt-2 border-t">
              <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                <i data-lucide="save" class="h-4 w-4"></i>
                Salvar Alterações
              </button>
            </div>
          </form>
        @empty
          <div class="rounded-md border p-8 text-center text-sm text-muted-foreground">
            <i data-lucide="inbox" class="h-12 w-12 mx-auto mb-3 text-gray-300"></i>
            <p>Nenhum status cadastrado ainda.</p>
          </div>
        @endforelse
      </div>
    </div>

  </div>

  <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
    <div class="flex flex-col space-y-1.5 p-6 border-b">
      <h3 class="text-2xl font-semibold leading-none tracking-tight">Prévia da Mensagem</h3>
      <p class="text-sm text-muted-foreground">Visualize como a mensagem será enviada ao cliente no WhatsApp</p>
    </div>
    <div class="p-6">
      <div class="max-w-2xl mx-auto">
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-4 shadow-lg">
          <div class="bg-white rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b">
              <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                <i data-lucide="store" class="h-5 w-5 text-white"></i>
              </div>
              <div>
                <div class="font-semibold text-sm">{{ config('app.name', 'Sua Loja') }}</div>
                <div class="text-xs text-muted-foreground">Online</div>
              </div>
            </div>
            <div class="space-y-1">
              <div class="inline-block bg-green-500 text-white rounded-lg rounded-tl-none px-4 py-2 max-w-full">
                <div class="text-xs opacity-75 mb-1">Pedido #123</div>
                <div id="previewContent" class="whitespace-pre-wrap text-sm">Selecione um template ou edite o conteúdo para visualizar a mensagem.</div>
                <div class="text-xs opacity-75 mt-2 text-right">{{ date('H:i') }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
          <div class="flex items-start gap-3">
            <i data-lucide="info" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-blue-900">
              <p class="font-semibold mb-1">Variáveis Disponíveis:</p>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-2">
                <code class="bg-white px-2 py-1 rounded text-xs" title="Nome do cliente">{nome}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Número do pedido">{pedido}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Valor total">{total}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Status do pedido">{status}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Link do WhatsApp">{link}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Data do pedido">{data}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Horário">{horario}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Endereço de entrega">{endereco}</code>
                <code class="bg-white px-2 py-1 rounded text-xs" title="Forma de pagamento">{pagamento}</code>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Criação/Edição de Template -->
<div id="templateModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden animate-in zoom-in-95 duration-200">
    <div class="flex items-center justify-between p-6 border-b">
      <div>
        <h3 class="text-2xl font-semibold" id="modalTitle">Novo Template</h3>
        <p class="text-sm text-muted-foreground mt-1">Crie ou edite um modelo de mensagem</p>
      </div>
      <button onclick="closeTemplateModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
        <i data-lucide="x" class="h-5 w-5"></i>
      </button>
    </div>
    
    <form action="{{ route('dashboard.settings.status-templates.template.save') }}" method="POST" class="overflow-y-auto" style="max-height: calc(90vh - 180px);">
      @csrf
      <input type="hidden" name="id" id="modal_tpl_id" value="">
      
      <div class="p-6 space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <label class="text-sm font-medium flex items-center gap-2">
              <i data-lucide="tag" class="h-4 w-4"></i>
              Identificador do Template
            </label>
            <input name="slug" id="modal_tpl_slug" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20" placeholder="ex: pedido_confirmado" required>
            <p class="text-xs text-muted-foreground">Use apenas letras minúsculas, números e underline</p>
          </div>
          
          <div class="space-y-2">
            <label class="text-sm font-medium">Status do Template</label>
            <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
              <input type="checkbox" name="active" id="modal_tpl_active" value="1" checked class="rounded text-primary focus:ring-primary w-5 h-5">
              <div>
                <span class="text-sm font-medium">Template Ativo</span>
                <p class="text-xs text-muted-foreground">Template estará disponível para uso</p>
              </div>
            </label>
          </div>
        </div>
        
        <div class="space-y-2">
          <label class="text-sm font-medium flex items-center gap-2">
            <i data-lucide="message-square" class="h-4 w-4"></i>
            Conteúdo da Mensagem
          </label>
          <textarea name="content" id="modal_tpl_content" rows="8" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary/20 font-mono" placeholder="Olá {nome}!\n\nSeu pedido #{pedido} foi confirmado.\nTotal: {total}\n\nObrigado pela preferência!" required></textarea>
          <div class="flex items-center justify-between">
            <p class="text-xs text-muted-foreground">Use as variáveis listadas abaixo para personalizar</p>
            <span id="charCount" class="text-xs text-muted-foreground">0 caracteres</span>
          </div>
        </div>
        
        <div class="p-4 bg-gray-50 rounded-lg border">
          <p class="text-sm font-medium mb-3">Variáveis Disponíveis - Clique para inserir:</p>
          <div class="grid grid-cols-3 gap-2">
            <button type="button" onclick="insertVariable('{nome}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{nome}</button>
            <button type="button" onclick="insertVariable('{pedido}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{pedido}</button>
            <button type="button" onclick="insertVariable('{total}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{total}</button>
            <button type="button" onclick="insertVariable('{status}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{status}</button>
            <button type="button" onclick="insertVariable('{link}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{link}</button>
            <button type="button" onclick="insertVariable('{data}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{data}</button>
            <button type="button" onclick="insertVariable('{horario}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{horario}</button>
            <button type="button" onclick="insertVariable('{endereco}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{endereco}</button>
            <button type="button" onclick="insertVariable('{pagamento}')" class="px-3 py-2 bg-white border rounded-md text-xs hover:bg-primary hover:text-white transition-colors">{pagamento}</button>
          </div>
        </div>
        
        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
          <p class="text-sm font-medium mb-2 text-green-900">Preview em Tempo Real:</p>
          <div class="bg-white rounded-lg p-3 text-sm whitespace-pre-wrap font-mono" id="modalPreview">Digite algo para ver o preview...</div>
        </div>
      </div>
      
      <div class="flex items-center justify-end gap-3 p-6 border-t bg-gray-50">
        <button type="button" onclick="closeTemplateModal()" class="px-4 py-2 rounded-md border hover:bg-gray-100 text-sm font-medium transition-colors">
          Cancelar
        </button>
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 text-sm font-medium transition-colors">
          <i data-lucide="save" class="h-4 w-4"></i>
          Salvar Template
        </button>
      </div>
    </form>
  </div>
</div>
@push('scripts')
<script>
// Funções do Modal
function openTemplateModal() {
  document.getElementById('templateModal').classList.remove('hidden');
  document.getElementById('templateModal').classList.add('flex');
  document.getElementById('modalTitle').textContent = 'Novo Template';
  document.getElementById('modal_tpl_id').value = '';
  document.getElementById('modal_tpl_slug').value = '';
  document.getElementById('modal_tpl_content').value = '';
  document.getElementById('modal_tpl_active').checked = true;
  updateModalPreview();
  updateCharCount();
}

function closeTemplateModal() {
  document.getElementById('templateModal').classList.add('hidden');
  document.getElementById('templateModal').classList.remove('flex');
}

function insertVariable(variable) {
  const textarea = document.getElementById('modal_tpl_content');
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const text = textarea.value;
  const before = text.substring(0, start);
  const after = text.substring(end, text.length);
  
  textarea.value = before + variable + after;
  textarea.selectionStart = textarea.selectionEnd = start + variable.length;
  textarea.focus();
  
  updateModalPreview();
  updateCharCount();
}

function updateCharCount() {
  const content = document.getElementById('modal_tpl_content')?.value || '';
  const count = content.length;
  const charCountEl = document.getElementById('charCount');
  if (charCountEl) {
    charCountEl.textContent = count + ' caracteres';
    if (count > 1000) {
      charCountEl.classList.add('text-red-600', 'font-semibold');
    } else {
      charCountEl.classList.remove('text-red-600', 'font-semibold');
    }
  }
}

function updateModalPreview() {
  const content = document.getElementById('modal_tpl_content')?.value || '';
  const preview = content
    .replace(/{nome}/g, 'Maria Silva')
    .replace(/{pedido}/g, '123')
    .replace(/{total}/g, 'R$ 99,90')
    .replace(/{status}/g, 'Confirmado')
    .replace(/{link}/g, 'https://wa.me/5571999999999')
    .replace(/{data}/g, new Date().toLocaleDateString('pt-BR'))
    .replace(/{horario}/g, new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}))
    .replace(/{endereco}/g, 'Rua Exemplo, 123 - Bairro - Cidade/UF')
    .replace(/{pagamento}/g, 'Cartão de Crédito');
  
  const previewEl = document.getElementById('modalPreview');
  if (previewEl) {
    previewEl.textContent = preview || 'Digite algo para ver o preview...';
  }
}

// Editar template
async function editTemplate(id) {
  try {
    const res = await fetch('{{ url('/dashboard/settings/status-templates/template') }}/' + id);
    const data = await res.json();
    
    if (data && !data.error) {
      document.getElementById('modal_tpl_id').value = data.id;
      document.getElementById('modal_tpl_slug').value = data.slug || '';
      document.getElementById('modal_tpl_content').value = data.content || '';
      document.getElementById('modal_tpl_active').checked = !!(data.active);
      document.getElementById('modalTitle').textContent = 'Editar Template';
      
      updateModalPreview();
      updateCharCount();
      
      // Abrir modal
      document.getElementById('templateModal').classList.remove('hidden');
      document.getElementById('templateModal').classList.add('flex');
    }
  } catch (e) {
    console.error('Erro ao carregar template:', e);
    alert('Erro ao carregar template. Tente novamente.');
  }
}

// Preview em tempo real - página principal
function updatePreview() {
  const content = document.getElementById('tpl_content')?.value || '';
  const preview = content
    .replace(/{nome}/g, 'Maria Silva')
    .replace(/{pedido}/g, '123')
    .replace(/{total}/g, 'R$ 99,90')
    .replace(/{status}/g, 'Confirmado')
    .replace(/{link}/g, 'https://wa.me/5571999999999')
    .replace(/{data}/g, new Date().toLocaleDateString('pt-BR'))
    .replace(/{horario}/g, new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}))
    .replace(/{endereco}/g, 'Rua Exemplo, 123 - Bairro - Cidade/UF')
    .replace(/{pagamento}/g, 'Cartão de Crédito');
  
  const previewEl = document.getElementById('previewContent');
  if (previewEl) {
    previewEl.textContent = preview || 'Selecione um template ou edite o conteúdo para visualizar a mensagem.';
  }
}

// Preview de template ao selecionar no status
function previewTemplateInStatus(selectElement) {
  const selectedOption = selectElement.options[selectElement.selectedIndex];
  const content = selectedOption.getAttribute('data-content') || '';
  
  if (content) {
    const preview = content
      .replace(/{nome}/g, 'Maria Silva')
      .replace(/{pedido}/g, '123')
      .replace(/{total}/g, 'R$ 99,90')
      .replace(/{status}/g, 'Confirmado')
      .replace(/{link}/g, 'https://wa.me/5571999999999')
      .replace(/{data}/g, new Date().toLocaleDateString('pt-BR'))
      .replace(/{horario}/g, new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}))
      .replace(/{endereco}/g, 'Rua Exemplo, 123 - Bairro - Cidade/UF')
      .replace(/{pagamento}/g, 'Cartão de Crédito');
    
    const previewEl = document.getElementById('previewContent');
    if (previewEl) {
      previewEl.textContent = preview;
    }
  }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Preview no modal
  const modalContent = document.getElementById('modal_tpl_content');
  if (modalContent) {
    modalContent.addEventListener('input', function() {
      updateModalPreview();
      updateCharCount();
    });
  }
  
  // Preview na página principal
  const content = document.getElementById('tpl_content');
  if (content) {
    content.addEventListener('input', updatePreview);
  }
  
  // Fechar modal ao clicar fora
  const modal = document.getElementById('templateModal');
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeTemplateModal();
      }
    });
  }
  
  // Tecla ESC para fechar modal
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const modal = document.getElementById('templateModal');
      if (modal && !modal.classList.contains('hidden')) {
        closeTemplateModal();
      }
    }
  });
  
  // Inicializar preview
  updatePreview();
  updateModalPreview();
  updateCharCount();
});
</script>
@endpush
@endsection



