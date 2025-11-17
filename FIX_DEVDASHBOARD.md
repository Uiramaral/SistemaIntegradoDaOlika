# 🔧 Fix: devdashboard apontando para pedido

## Problema
O subdomínio `devdashboard.menuolika.com.br` está redirecionando para o pedido ao invés do dashboard.

## Soluções Aplicadas

1. **Ajuste na rota raiz genérica**: A rota raiz não interfere mais quando há subdomínios configurados
2. **TrustHosts atualizado**: Subdomínios de desenvolvimento sempre permitidos

## Verificações Necessárias

### 1. Limpar cache de rotas
Execute no servidor:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 2. Verificar arquivo .env
No ambiente de desenvolvimento, certifique-se de:
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=https://devdashboard.menuolika.com.br

DASHBOARD_DOMAIN=devdashboard.menuolika.com.br
PEDIDO_DOMAIN=devpedido.menuolika.com.br
```

### 3. Verificar se o subdomínio está configurado
No cPanel do HostGator:
- Subdomínio: `devdashboard`
- Document Root: `/public_html/desenvolvimento/public`

### 4. Testar diretamente
Acesse:
```
https://devdashboard.menuolika.com.br/
```

Deve redirecionar para `/login` (pois requer autenticação).

## Se ainda não funcionar

1. Verifique os logs do Laravel: `storage/logs/laravel.log`
2. Execute `php artisan route:list | grep dashboard` para ver se as rotas estão registradas
3. Verifique se há algum middleware bloqueando

