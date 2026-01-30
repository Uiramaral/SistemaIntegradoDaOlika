@extends('dashboard.layouts.app')

@section('page_title', 'Assistente IA')
@section('page_subtitle', 'Sua assistente para cardápio, marketing e segurança alimentar')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="bg-card rounded-xl border border-border flex flex-col min-h-[calc(100vh-14rem)] sm:min-h-[420px] overflow-hidden shadow-sm">
        {{-- Header --}}
        <div class="p-4 border-b border-border flex flex-wrap items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <i data-lucide="bot" class="w-5 h-5 text-primary"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-foreground">{{ $assistantName ?? 'ChefIA' }}</h3>
                    <p class="text-xs text-muted-foreground">● Online</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label for="assistente-context" class="text-xs font-medium text-muted-foreground">Contexto:</label>
                <select id="assistente-context" class="h-9 rounded-lg border border-border bg-muted/30 text-sm px-3 focus:ring-2 focus:ring-primary/20 focus:border-primary w-auto min-w-[160px]">
                    @foreach($contextLabels ?? [] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Messages Area --}}
        <div id="assistente-messages" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin">
            <div class="flex gap-3 max-w-[85%]">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <i data-lucide="bot" class="w-4 h-4 text-primary"></i>
                </div>
                <div class="bg-muted/60 rounded-2xl rounded-tl-sm px-4 py-3">
                    <p class="text-sm text-foreground">
                        Olá! Sou a <strong>{{ $assistantName ?? 'ChefIA' }}</strong>. Escolha um contexto acima e me pergunte o que quiser — cardápio, marketing, segurança alimentar ou geral.
                    </p>
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="p-4 border-t border-border shrink-0">
            <div id="assistente-form" class="flex gap-2" data-ask-url="{{ route('dashboard.assistente-ia.ask') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="text"
                       id="assistente-input"
                       placeholder="Digite sua pergunta..."
                       class="form-input flex-1 h-12 rounded-xl border-border bg-muted/30 focus:bg-white"
                       maxlength="4000"
                       autocomplete="off">
                <button type="button"
                        id="assistente-send"
                        class="h-12 px-5 bg-primary text-primary-foreground rounded-xl hover:bg-primary/90 flex items-center justify-center shrink-0 transition-colors font-medium gap-2"
                        aria-label="Enviar">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span class="hidden sm:inline">Enviar</span>
                </button>
            </div>
            <p id="assistente-status" class="text-xs mt-2 min-h-[1.25rem]" role="status" aria-live="polite"></p>
            <p class="text-xs text-muted-foreground mt-1">Respostas usam o contexto do seu estabelecimento. <a href="{{ route('dashboard.assistente-ia.test') }}?prompt=ola&context=default" id="assistente-test-link" target="_blank" rel="noopener" class="text-primary hover:underline">Testar backend (abrir em nova aba)</a>.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    // Variáveis globais para acesso
    var sendBtn, input, form, statusEl, messagesEl, contextSelect, askUrl, assistantName;
    
    function run() {
        console.log('AssistenteIA: inicializando...');
        
        form = document.getElementById('assistente-form');
        input = document.getElementById('assistente-input');
        sendBtn = document.getElementById('assistente-send');
        statusEl = document.getElementById('assistente-status');
        messagesEl = document.getElementById('assistente-messages');
        contextSelect = document.getElementById('assistente-context');
        assistantName = {!! json_encode($assistantName ?? 'ChefIA') !!};
        askUrl = (form && form.getAttribute('data-ask-url')) ? form.getAttribute('data-ask-url') : {!! json_encode(route('dashboard.assistente-ia.ask')) !!};
        
        console.log('AssistenteIA: elementos encontrados', {
            form: !!form,
            input: !!input,
            sendBtn: !!sendBtn,
            statusEl: !!statusEl,
            messagesEl: !!messagesEl,
            contextSelect: !!contextSelect,
            askUrl: askUrl
        });

        if (!sendBtn) {
            console.error('AssistenteIA: ERRO - sendBtn não encontrado!');
            return;
        }
        
        if (!input) {
            console.error('AssistenteIA: ERRO - input não encontrado!');
            return;
        }

        function setStatus(msg, isError) {
            if (!statusEl) return;
            statusEl.textContent = msg || '';
            statusEl.className = 'text-xs mt-2 min-h-[1.25rem] ' + (isError ? 'text-destructive' : 'text-muted-foreground');
        }

        function scrollToBottom() {
            if (messagesEl) {
                setTimeout(function() {
                    messagesEl.scrollTop = messagesEl.scrollHeight;
                }, 100);
            }
        }

        function addMessage(role, text, isError) {
            if (!messagesEl) {
                console.error('AssistenteIA: messagesEl não encontrado');
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'flex gap-3 max-w-[85%] ' + (role === 'user' ? 'ml-auto flex-row-reverse' : '');
            wrap.setAttribute('data-message-role', role);
            wrap.setAttribute('data-message-time', Date.now());
            var icon = role === 'user'
                ? '<div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0 text-primary-foreground text-sm font-bold">' + (assistantName ? String(assistantName).charAt(0) : 'U') + '</div>'
                : '<div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0"><i data-lucide="bot" class="w-4 h-4 text-primary"></i></div>';
            var bubble = isError
                ? 'bg-destructive/10 text-destructive rounded-2xl px-4 py-3'
                : (role === 'user' ? 'bg-primary text-primary-foreground rounded-2xl rounded-tr-sm px-4 py-3' : 'bg-muted/60 rounded-2xl rounded-tl-sm px-4 py-3');
            wrap.innerHTML = icon + '<div class="' + bubble + '"><p class="text-sm whitespace-pre-wrap">' + escapeHtml(text) + '</p></div>';
            messagesEl.appendChild(wrap);
            var icons = wrap.querySelectorAll('[data-lucide]');
            if (typeof lucide !== 'undefined' && icons.length) {
                lucide.createIcons({ icons: icons });
            }
            scrollToBottom();
        }

        function escapeHtml(s) {
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        // Armazenar histórico de mensagens
        var conversationHistory = [];
        
        function send() {
            console.log('AssistenteIA: send() chamado');
            try {
                // Verificar novamente se os elementos existem
                if (!sendBtn || !input) {
                    console.error('AssistenteIA: elementos não encontrados em send()');
                    return;
                }
                var prompt = (input && input.value ? input.value : '').trim();
                console.log('AssistenteIA: prompt =', prompt);
                
                if (!prompt) {
                    console.log('AssistenteIA: prompt vazio, ignorando');
                    return;
                }

                var context = (contextSelect && contextSelect.value) ? contextSelect.value : 'default';
                var tokEl = form ? form.querySelector('input[name="_token"]') : null;
                var tok = (tokEl && tokEl.value) ? tokEl.value : '';
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (!tok && meta) tok = meta.getAttribute('content') || '';

                // Coletar histórico das últimas mensagens (últimas 8 para não exceder limite)
                // IMPORTANTE: Coletar ANTES de adicionar a nova mensagem do usuário
                var history = [];
                if (messagesEl) {
                    // Buscar todas as mensagens que têm o atributo data-message-role (mensagens dinâmicas)
                    // OU usar seletor genérico para mensagens antigas
                    var messageElements = messagesEl.querySelectorAll('div[data-message-role], div.flex.gap-3');
                    var maxHistory = 8; // Últimas 8 mensagens (4 user + 4 assistant)
                    
                    console.log('AssistenteIA: elementos encontrados', { 
                        total: messageElements.length,
                        selector: 'div[data-message-role], div.flex.gap-3'
                    });
                    
                    // Coletar do mais antigo para o mais recente (exceto a mensagem de boas-vindas inicial)
                    // Começar do índice 1 para pular a mensagem de boas-vindas (índice 0)
                    for (var i = 1; i < messageElements.length && history.length < maxHistory; i++) {
                        var msgEl = messageElements[i];
                        
                        // Pular mensagem de boas-vindas (não tem data-message-role e é a primeira)
                        if (i === 0 && !msgEl.hasAttribute('data-message-role')) {
                            continue;
                        }
                        
                        // Determinar role - usar atributo data se disponível, senão usar classes
                        var role = msgEl.getAttribute('data-message-role');
                        if (!role) {
                            var isUser = msgEl.classList.contains('ml-auto') || msgEl.classList.contains('flex-row-reverse');
                            role = isUser ? 'user' : 'assistant';
                        }
                        
                        // Buscar o texto - a estrutura é: div.flex > div (bubble) > p.text-sm
                        var bubbleDiv = msgEl.querySelector('div.bg-primary, div.bg-muted, div.bg-destructive\\/10');
                        var textEl = bubbleDiv ? bubbleDiv.querySelector('p') : msgEl.querySelector('p.text-sm, p');
                        
                        if (textEl && textEl.textContent) {
                            var text = textEl.textContent.trim();
                            
                            // Ignorar mensagens muito curtas ou vazias
                            if (text && text.length > 3) {
                                // Verificar se não é mensagem de erro
                                var isError = msgEl.querySelector('.bg-destructive') || msgEl.querySelector('.text-destructive');
                                if (!isError) {
                                    history.push({
                                        role: role,
                                        text: text
                                    });
                                    console.log('AssistenteIA: mensagem adicionada ao histórico', {
                                        index: i,
                                        role: role,
                                        textLength: text.length,
                                        textPreview: text.substring(0, 50) + '...'
                                    });
                                }
                            }
                        } else {
                            console.warn('AssistenteIA: não foi possível extrair texto da mensagem', { 
                                index: i,
                                hasBubble: !!bubbleDiv,
                                hasTextEl: !!textEl
                            });
                        }
                    }
                }
                
                console.log('AssistenteIA: histórico coletado', { 
                    count: history.length,
                    history: history.map(function(h) { 
                        return { 
                            role: h.role, 
                            textLength: h.text.length,
                            textPreview: h.text.substring(0, 50) + '...' 
                        }; 
                    })
                });

                console.log('AssistenteIA: preparando envio', {
                    prompt: prompt.substring(0, 50) + '...',
                    context: context,
                    hasToken: !!tok,
                    historyLength: history.length
                });

                sendBtn.disabled = true;
                if (input) input.disabled = true;
                addMessage('user', prompt);
                if (input) input.value = '';
                setStatus('Enviando…');

                // Adicionar histórico ao body
                var body = 'prompt=' + encodeURIComponent(prompt) + '&context=' + encodeURIComponent(context) + '&_token=' + encodeURIComponent(tok);
                if (history.length > 0) {
                    var historyJson = JSON.stringify(history);
                    body += '&history=' + encodeURIComponent(historyJson);
                    console.log('AssistenteIA: histórico será enviado', {
                        historyCount: history.length,
                        historyJsonLength: historyJson.length,
                        historyJsonPreview: historyJson.substring(0, 200) + '...'
                    });
                } else {
                    console.log('AssistenteIA: nenhum histórico para enviar');
                }

                console.log('AssistenteIA: fazendo fetch para', askUrl, 'body length:', body.length);
                fetch(askUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': tok,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body,
                    credentials: 'same-origin'
                })
                .then(function(r) {
                    console.log('AssistenteIA: resposta recebida, status:', r.status);
                    return r.text().then(function(text) {
                        var data = null;
                        try { 
                            data = text ? JSON.parse(text) : {}; 
                        } catch (e) {
                            console.error('AssistenteIA: erro ao fazer parse do JSON', e, text);
                        }
                        console.log('AssistenteIA: response data', data);
                        return { ok: r.ok, status: r.status, data: data, raw: text };
                    });
                })
                .then(function(res) {
                    console.log('AssistenteIA: processando resposta', res);
                    setStatus('');
                    var d = res.data;
                    if (res.ok && d && d.success && d.message) {
                        addMessage('assistant', d.message, false);
                        // Adicionar ao histórico (será coletado na próxima requisição)
                        return;
                    }
                    var err = (d && d.error) ? d.error : (d && d.message) ? d.message : null;
                    if (!err && res.status === 419) err = 'Sessão expirada. Atualize a página e tente de novo.';
                    if (!err && res.status >= 500) err = 'Erro no servidor (HTTP ' + res.status + '). Tente mais tarde.';
                    if (!err && res.status >= 400) err = 'Erro HTTP ' + res.status + '. ' + (err || 'Tente novamente.');
                    if (!err) err = 'Não foi possível obter uma resposta. Tente novamente.';
                    console.error('AssistenteIA: erro na resposta', err);
                    addMessage('assistant', err, true);
                    setStatus(err, true);
                })
                .catch(function(err) {
                    console.error('AssistenteIA: erro no fetch', err);
                    setStatus('Erro de conexão.', true);
                    addMessage('assistant', 'Erro de conexão. Verifique a rede e tente novamente.', true);
                })
                .finally(function() {
                    console.log('AssistenteIA: finalizando, reabilitando botão');
                    sendBtn.disabled = false;
                    if (input) { 
                        input.disabled = false; 
                        input.focus(); 
                    }
                });
            } catch (e) {
                console.error('AssistenteIA: erro em send()', e);
                setStatus('Erro: ' + (e.message || 'Erro desconhecido'), true);
                addMessage('assistant', 'Erro ao enviar: ' + (e.message || 'Erro desconhecido'), true);
                sendBtn.disabled = false;
                if (input) { 
                    input.disabled = false; 
                    input.focus(); 
                }
            }
        }

        // Expor função globalmente ANTES de adicionar listeners
        window.assistenteIASend = send;
        
        // Adicionar onclick inline como fallback PRIMEIRO
        sendBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('AssistenteIA: onclick executado!');
            if (window.assistenteIASend) {
                window.assistenteIASend();
            } else {
                console.error('AssistenteIA: função send não disponível');
            }
            return false;
        };
        
        // Adicionar event listener ao botão também
        console.log('AssistenteIA: adicionando event listener ao botão');
        sendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('AssistenteIA: botão clicado via addEventListener!');
            send();
        });
        
        // Adicionar event listener ao input (Enter)
        console.log('AssistenteIA: adicionando event listener ao input');
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                console.log('AssistenteIA: Enter pressionado');
                send();
            }
        });

        // Inicializar ícones Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        console.log('AssistenteIA: inicialização completa');
    }
    
    // Aguardar DOM e scripts carregarem
    function init() {
        try {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(run, 200);
                });
            } else {
                // DOM já está pronto, mas aguardar um pouco para garantir que tudo está carregado
                setTimeout(run, 200);
            }
        } catch (e) {
            console.error('AssistenteIA: Erro na inicialização', e);
        }
    }
    
    // Tentar inicializar imediatamente e também após o carregamento completo
    init();
    
    // Fallback: tentar novamente após um tempo maior
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (!window.assistenteIASend) {
                console.warn('AssistenteIA: tentando reinicializar...');
                run();
            }
        }, 500);
    });
})();
</script>
@endpush
@endsection
