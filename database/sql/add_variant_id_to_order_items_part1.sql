-- SQL Parte 1: Adicionar coluna variant_id
-- Execute este SQL primeiro

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order_items' 
    AND COLUMN_NAME = 'variant_id'
);

-- Adicionar coluna se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `order_items` ADD COLUMN `variant_id` BIGINT UNSIGNED NULL AFTER `product_id`',
    'SELECT "Coluna variant_id já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

