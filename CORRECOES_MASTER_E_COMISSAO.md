# ✅ Correções Aplicadas - Master e Comissão Mercado Pago

## 📋 Resumo das Alterações

### 1. **Erro do Relacionamento WhatsApp Corrigido**
- **Arquivo**: `app/Http/Controllers/Master/ClientsManagementController.php` linha 117
- **Problema**: Chamada ao relacionamento `whatsappInstances` que não existe
- **Solução**: Corrigido para `whatsappInstanceUrls` (relacionamento correto)

### 2. **Novo Campo: is_master**
- Permite marcar um estabelecimento como "Master" (proprietário do SaaS)
- Estabelecimentos master **NÃO pagam comissão** por venda
- Badge visual especial no formulário 🏢

### 3. **Sistema de Comissão Mercado Pago**
- Campo `mercadopago_commission_enabled`: Habilita/desabilita comissão
- Campo `mercadopago_commission_amount`: Valor fixo da comissão (padrão R$ 0,49)
- Funciona via **Application Fee** do Mercado Pago
- Comissão é cobrada **automaticamente** em cada venda

---

## 🗄️ Migração de Banco de Dados

### Arquivo criado:
```
database/sql/add_master_and_commission_fields.sql
```

### Execute no phpMyAdmin:
1. Acesse seu banco de dados de produção
2. Abra a aba "SQL"
3. Cole o conteúdo do arquivo `add_master_and_commission_fields.sql`
4. Clique em "Executar"

### O que a migração faz:
✅ Adiciona campo `is_master` (BOOLEAN)
✅ Adiciona campo `mercadopago_commission_enabled` (BOOLEAN)
✅ Adiciona campo `mercadopago_commission_amount` (DECIMAL 10,2)
✅ Adiciona campos `email` e `phone` se não existirem
✅ Cria índices para performance
✅ Marca automaticamente o cliente `menuolika` ou id=1 como master
✅ Adiciona configurações no `master_settings`

---

## 📝 Arquivos Modificados

### 1. **Model Client**
- **Arquivo**: `app/Models/Client.php`
- **Mudanças**:
  - Adicionados 5 campos no `$fillable`: `email`, `phone`, `is_master`, `mercadopago_commission_enabled`, `mercadopago_commission_amount`
  - Adicionados 3 campos no `$casts`
  - Novos métodos:
    - `isMaster()` - Verifica se é master
    - `hasMercadoPagoCommission()` - Verifica se tem comissão habilitada
    - `getMercadoPagoCommissionAmount()` - Retorna valor da comissão

### 2. **Controller ClientsManagementController**
- **Arquivo**: `app/Http/Controllers/Master/ClientsManagementController.php`
- **Mudanças**:
  - **Linha 117**: Corrigido `whatsappInstances` → `whatsappInstanceUrls`
  - **Método update()**: Adicionada validação e processamento dos novos campos
  - **Lógica**: Master nunca pode ter comissão habilitada (segurança)

### 3. **View do Formulário**
- **Arquivo**: `resources/views/master/clients/form.blade.php`
- **Mudanças**:
  - Nova seção "Estabelecimento Master" com checkbox destacado
  - Nova seção "Comissão por Venda (Mercado Pago)"
  - Campo valor da comissão aparece/esconde dinamicamente
  - Validação client-side e tooltips explicativos

---

## 🚀 Deploy

### Passo 1: Fazer push do código
```powershell
cd "c:\Users\uira_\OneDrive\Documentos\Sistema Unificado da Olika"
git add .
git commit -m "feat: Add master client and Mercado Pago commission system"
git push
```

### Passo 2: Executar no servidor
1. Fazer pull no servidor de produção
2. Executar a migração SQL no banco de dados
3. Limpar cache do Laravel:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

---

## 🎯 Como Usar

### Marcar um estabelecimento como Master:
1. Acesse o painel Master: `/master/clients`
2. Clique em "Editar" no estabelecimento
3. Marque o checkbox "🏢 Estabelecimento Master"
4. Salve

### Habilitar comissão para um cliente:
1. Edite o estabelecimento (não master)
2. Na seção "Comissão por Venda":
   - Marque "Habilitar comissão por venda"
   - Defina o valor (padrão R$ 0,49)
3. Salve

### Como funciona a comissão:
- A comissão é cobrada **automaticamente** via Application Fee do Mercado Pago
- O valor é deduzido da venda e transferido para a conta master do SaaS
- O estabelecimento recebe: `Valor da venda - Comissão do SaaS - Taxas do MP`
- Exemplo: Venda de R$ 100,00
  - Comissão SaaS: R$ 0,49
  - Taxa MP (4,99%): R$ 4,99
  - Estabelecimento recebe: R$ 94,52

---

## ⚠️ Observações Importantes

1. **Estabelecimentos Master** nunca pagam comissão (proteção no código)
2. **Valor padrão** da comissão é R$ 0,49 (configurável)
3. **Application Fee** precisa ser configurado no Mercado Pago API
4. **Clientes inativos** não sofrem cobrança de comissão
5. A comissão é por **transação**, não por período

---

## 🔍 Próximos Passos (Opcional)

### Integração com Mercado Pago API:
Para aplicar a comissão automaticamente, será necessário modificar o `MercadoPagoService.php` para incluir o parâmetro `application_fee` nos pagamentos:

```php
$payment->application_fee = $client->getMercadoPagoCommissionAmount();
```

### Dashboard de Comissões:
Criar relatório no Master Dashboard mostrando:
- Total de comissões recebidas no mês
- Comissões por estabelecimento
- Histórico de comissões

---

## ✅ Checklist de Deploy

- [ ] Código commitado e enviado ao repositório
- [ ] Pull realizado no servidor de produção
- [ ] Migração SQL executada com sucesso
- [ ] Cache do Laravel limpo
- [ ] Testado edição de cliente no painel Master
- [ ] Verificado que master não pode ter comissão habilitada
- [ ] Testado toggle do campo de valor da comissão

---

## 📞 Suporte

Se houver qualquer erro durante o deploy, verifique:
1. Log do Laravel: `storage/logs/laravel.log`
2. Log do servidor web
3. Confirmação de que a migração SQL foi executada
4. Estrutura da tabela `clients` no banco: `SHOW COLUMNS FROM clients;`
