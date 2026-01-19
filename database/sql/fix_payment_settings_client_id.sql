-- ================================================================
-- CORREÇÃO: payment_settings - Adicionar client_id
-- ================================================================
-- Se a coluna já existir, você verá "Duplicate column name" - pode ignorar

-- Adicionar coluna client_id
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

-- Ou se o nome for diferente:
-- ALTER TABLE `payment_settings` DROP INDEX `key`;

-- Criar nova constraint única incluindo client_id
-- Se der erro "Duplicate key name", significa que já existe - pode ignorar
ALTER TABLE `payment_settings`
ADD UNIQUE KEY `unique_client_key` (`client_id`, `key`);

-- Associar registros existentes ao cliente master (ID 1)
UPDATE `payment_settings` 
SET `client_id` = 1 
WHERE `client_id` IS NULL;
