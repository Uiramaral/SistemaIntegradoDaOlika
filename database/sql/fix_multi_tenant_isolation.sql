-- ================================================================
-- CORREÇÃO: Isolamento Multi-Tenant
-- ================================================================
-- Este script garante que todas as tabelas tenham client_id e
-- que os dados existentes sejam associados corretamente
-- ================================================================
-- IMPORTANTE: Execute cada passo separadamente se algum falhar
-- ================================================================

-- ================================================================
-- PASSO 1: payment_settings - Adicionar client_id
-- ================================================================
-- ⚠️ Se você receber "Duplicate column name", significa que a coluna já existe
-- Neste caso, PULE este comando e continue para os próximos (índice, constraint, etc.)

ALTER TABLE `payment_settings` 
ADD COLUMN `client_id` BIGINT UNSIGNED NULL DEFAULT NULL 
COMMENT 'ID do estabelecimento (multi-tenant)' 
AFTER `id`;

-- Criar índice (pode dar erro se já existir - pode ignorar)
ALTER TABLE `payment_settings`
ADD INDEX `idx_payment_settings_client_id` (`client_id`);

-- Remover constraint única antiga (se existir)
-- Se der erro "Can't DROP", significa que não existe - pode ignorar
ALTER TABLE `payment_settings` 
DROP INDEX `payment_settings_key_unique`;

-- Ou se o nome for diferente, tente:
-- ALTER TABLE `payment_settings` DROP INDEX `key`;

-- Criar nova constraint única incluindo client_id
-- Se der erro "Duplicate key name", significa que já existe - pode ignorar
ALTER TABLE `payment_settings`
ADD UNIQUE KEY `unique_client_key` (`client_id`, `key`);

-- Associar registros existentes ao cliente master (ID 1)
UPDATE `payment_settings` 
SET `client_id` = 1 
WHERE `client_id` IS NULL;

-- ================================================================
-- PASSO 2: whatsapp_instances - Adicionar client_id
-- ================================================================

ALTER TABLE `whatsapp_instances` 
ADD COLUMN `client_id` BIGINT UNSIGNED NULL DEFAULT NULL 
COMMENT 'ID do estabelecimento (multi-tenant)' 
AFTER `id`;

ALTER TABLE `whatsapp_instances`
ADD INDEX `idx_whatsapp_instances_client_id` (`client_id`);

UPDATE `whatsapp_instances` 
SET `client_id` = 1 
WHERE `client_id` IS NULL;

-- ================================================================
-- PASSO 3: whatsapp_settings - Adicionar client_id
-- ================================================================

ALTER TABLE `whatsapp_settings` 
ADD COLUMN `client_id` BIGINT UNSIGNED NULL DEFAULT NULL 
COMMENT 'ID do estabelecimento (multi-tenant)' 
AFTER `id`;

ALTER TABLE `whatsapp_settings`
ADD INDEX `idx_whatsapp_settings_client_id` (`client_id`);

UPDATE `whatsapp_settings` 
SET `client_id` = 1 
WHERE `client_id` IS NULL;

-- ================================================================
-- NOTA: product_wholesale_prices não precisa de client_id
-- ================================================================
-- Esta tabela está relacionada com products, que já tem client_id.
-- Os preços de revenda são filtrados através do relacionamento.
