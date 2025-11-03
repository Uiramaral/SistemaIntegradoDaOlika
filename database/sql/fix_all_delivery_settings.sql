-- ============================================
-- CONFIGURAÇÃO COMPLETA DE ENTREGA
-- ============================================

-- 1. Adicionar campo business_cep se não existir
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'settings' 
     AND COLUMN_NAME = 'business_cep') > 0,
    'SELECT "Coluna business_cep já existe - OK!" AS message;',
    'ALTER TABLE `settings` ADD COLUMN `business_cep` VARCHAR(10) NULL AFTER `business_longitude`;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Atualizar configuração de dias mínimos de antecedência para 2 dias
UPDATE `settings` SET `advance_order_days` = 2 WHERE `advance_order_days` IS NULL OR `advance_order_days` < 2;

-- 3. Atualizar delivery_schedules para usar 2 dias de antecedência por padrão
UPDATE `delivery_schedules` SET `delivery_lead_time_days` = 2 WHERE `delivery_lead_time_days` = 1;

-- 4. Verificar estrutura das tabelas
SELECT 'Configuração de entrega atualizada!' AS status;
