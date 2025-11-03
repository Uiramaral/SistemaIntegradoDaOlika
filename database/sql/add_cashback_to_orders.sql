-- Adicionar campos de cashback na tabela orders
-- IMPORTANTE: Execute este SQL apenas se as colunas não existirem

-- Verificar se cashback_used existe
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'orders' 
  AND COLUMN_NAME = 'cashback_used';

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `cashback_used` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT ''Cashback usado no pedido'' AFTER `discount_amount`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se cashback_earned existe
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'orders' 
  AND COLUMN_NAME = 'cashback_earned';

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `orders` ADD COLUMN `cashback_earned` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT ''Cashback gerado pelo pedido'' AFTER `cashback_used`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
