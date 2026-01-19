-- ================================================================
-- CORREÇÃO: whatsapp_settings - Adicionar client_id
-- ================================================================
-- Se a coluna já existir, você verá "Duplicate column name" - pode ignorar

ALTER TABLE `whatsapp_settings` 
ADD COLUMN `client_id` BIGINT UNSIGNED NULL DEFAULT NULL 
COMMENT 'ID do estabelecimento (multi-tenant)' 
AFTER `id`;

ALTER TABLE `whatsapp_settings`
ADD INDEX `idx_whatsapp_settings_client_id` (`client_id`);

UPDATE `whatsapp_settings` 
SET `client_id` = 1 
WHERE `client_id` IS NULL;
