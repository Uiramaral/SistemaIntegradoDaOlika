-- ============================================
-- AJUSTES DE AGENDAMENTO DE ENTREGA
-- ============================================

-- 1. Atualizar configuração de dias mínimos de antecedência para 2 dias
UPDATE `settings` SET `advance_order_days` = 2 WHERE `advance_order_days` IS NULL OR `advance_order_days` < 2;
-- Se não existir registro em settings, criar um
INSERT INTO `settings` (`advance_order_days`, `created_at`, `updated_at`)
SELECT 2, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `settings` LIMIT 1);

-- 2. Garantir que o campo scheduled_delivery_at existe na tabela orders
-- (Já existe na migration, mas vamos garantir)
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'orders' 
     AND COLUMN_NAME = 'scheduled_delivery_at') > 0,
    'SELECT "Coluna scheduled_delivery_at já existe - OK!" AS message;',
    'ALTER TABLE `orders` ADD COLUMN `scheduled_delivery_at` DATETIME NULL AFTER `observations`;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Atualizar delivery_schedules para usar 2 dias de antecedência por padrão
UPDATE `delivery_schedules` SET `delivery_lead_time_days` = 2 WHERE `delivery_lead_time_days` = 1;

-- 4. Verificar estrutura das tabelas
SELECT 'Configuração de agendamento atualizada!' AS status;
