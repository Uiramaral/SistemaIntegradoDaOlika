# Instruções para Configurar Tokens de Segurança

## Tokens Necessários

Adicione estas linhas no seu arquivo `.env`:

```env
# Tokens de segurança para utilitários do sistema
SYSTEM_CLEAR_TOKEN=OLIKA2025_CLEAR_SECURE
SYSTEM_FLUSH_TOKEN=OLIKA2025_FLUSH_SECURE

# Hosts dos subdomínios (opcional - para evitar strings fixas)
DASHBOARD_HOST=dashboard.menuolika.com.br
STORE_HOST=pedido.menuolika.com.br
```

## Como Usar

### Limpeza Rápida
```
https://pedido.menuolika.com.br/_tools/clear?t=OLIKA2025_CLEAR_SECURE
```

### Flush Completo
```
https://pedido.menuolika.com.br/__flush?t=OLIKA2025_FLUSH_SECURE
```

## Segurança

- **NUNCA** compartilhe estes tokens
- **ALTERE** os tokens por outros mais seguros
- Use apenas em produção quando necessário
- As rotas agora são protegidas e só funcionam com o token correto

## Verificação

Após adicionar os tokens, teste:
1. `https://pedido.menuolika.com.br/health-sistema` (deve retornar "ok-from-sistema")
2. `https://pedido.menuolika.com.br/__flush?t=SEU_TOKEN` (deve funcionar)
3. `https://pedido.menuolika.com.br/__flush` (deve dar 403 - acesso negado)
