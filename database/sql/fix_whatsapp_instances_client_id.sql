-- ================================================================
-- CORREÇÃO: whatsapp_instances - Adicionar client_id
-- ================================================================
-- Se a coluna já existir, você verá "Duplicate column name" - pode ignorar

ALTER TABLE `whatsapp_instances` 
ADD COLUMN `client_id` BIGINT UNSIGNED NULL DEFAULT NULL 
COMMENT 'ID do estabelecimento (multi-tenant)' 
AFTER `id`;

ALTER TABLE `whatsapp_instances`
ADD INDEX `idx_whatsapp_instances_client_id` (`client_id`);

UPDATE `whatsapp_instances` 
SET `client_id` = 1 
WHERE `client_id` IS NULL;
