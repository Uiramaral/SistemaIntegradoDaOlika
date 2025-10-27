# ✅ FRETE POR DISTÂNCIA - INSTRUÇÕES FINAIS

## 📋 RESUMO DAS ALTERAÇÕES

### 1. SQL (Executar no MySQL)
Execute o arquivo `SQL_CORRIGIDO_FINAL.sql` que contém apenas as colunas necessárias:
- `store_zip_code` e `store_number` na tabela `settings` (APÓS `business_full_address`)
- `pix_key`, `pix_name`, `pix_city` para configuração do PIX (opcional)

**NOTA IMPORTANTE:**
- `business_latitude`, `business_longitude` já existem ✓
- `delivery_fee_per_km`, `free_delivery_threshold`, `max_delivery_distance` já existem ✓
- `addresses.latitude` e `addresses.longitude` já existem ✓
- `order_items.custom_name` já existe ✓

### 2. Controller Atualizado
O arquivo `app/Http/Controllers/Dashboard/PDVController.php` foi atualizado com:
- ✅ Método `geocodeAddressIfNeeded()` usa `settings.google_maps_api_key` do banco
- ✅ Método `computeDeliveryFeeByDistance()` usa as colunas corretas:
  - `business_latitude`, `business_longitude` (para localização da loja)
  - `delivery_fee_per_km` (valor por km)
  - `free_delivery_threshold` (valor mínimo para frete grátis)
  - `max_delivery_distance` (raio máximo de entrega)

### 3. Rotas (Já Adicionadas)
- ✅ Rotas do PDV em `routes/api.php`
- ✅ Rotas de visualização em `routes/web.php`

### 4. Controllers Criados
- ✅ `OrderViewController.php` - Exibe detalhes do pedido com QR PIX
- ✅ `DebtsController.php` - Gerencia fiados do cliente

### 5. Blades a Criar
Criar manualmente (modelo fornecido no arquivo de documentação):
- `resources/views/dashboard/orders/show.blade.php`
- `resources/views/dashboard/customers/fiados.blade.php`

## 🚀 PASSOS PARA APLICAR

1. **Executar SQL:** Execute `SQL_CORRIGIDO_FINAL.sql` no MySQL
2. **Configurar loja:** Preencher coordenadas em `settings`:
   - `business_latitude`, `business_longitude`
   - `delivery_fee_per_km` (ex: 2.50)
   - `free_delivery_threshold` (ex: 150.00)
   - `max_delivery_distance` (ex: 15.00)
3. **Configurar PIX (Opcional):** Preencher `pix_key`, `pix_name`, `pix_city`
4. **Criar blades:** Usar modelos do arquivo de documentação
5. **Testar:** Testar cálculo de frete no PDV

## 📝 CHECKLIST FINAL

- [x] SQL corrigido criado (`SQL_CORRIGIDO_FINAL.sql`)
- [x] Controller atualizado com colunas corretas
- [x] Rotas adicionadas
- [x] Controllers criados
- [ ] SQL executado no banco
- [ ] Blades criadas
- [ ] Configuração da loja preenchida
- [ ] Testes realizados

## 🔄 COMO FUNCIONA

1. **Cálculo de Frete:**
   - PDV chama `/api/pdv/calc-frete`
   - Backend calcula distância (Haversine) entre loja e cliente
   - Aplica regras: raio máximo, frete grátis, taxa mínima
   - Retorna valor do frete em tempo real

2. **PIX:**
   - Gera código PIX (BR Code) localmente
   - Salva `pix_copia_cola` e `pix_expires_at` no pedido
   - Exibe QR Code na tela de detalhes

3. **Fiados:**
   - Cria registro em `customer_debts` (type='debit')
   - Cliente visualiza em `/dashboard/customers/{id}/fiados`
   - Usuário pode dar baixa criando registro de crédito

## 📂 ARQUIVOS MODIFICADOS/CRIADOS

### Criados:
- `SQL_CORRIGIDO_FINAL.sql`
- `INSTRUCOES_FINAIS.md` (este arquivo)
- `app/Http/Controllers/OrderViewController.php`
- `app/Http/Controllers/DebtsController.php`

### Modificados:
- `app/Http/Controllers/Dashboard/PDVController.php`
- `routes/api.php`
- `routes/web.php`

### A Criar:
- `resources/views/dashboard/orders/show.blade.php`
- `resources/views/dashboard/customers/fiados.blade.php`

