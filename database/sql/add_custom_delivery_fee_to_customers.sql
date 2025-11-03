-- Adiciona coluna de taxa de entrega fixa por cliente (se não existir)

-- customers.custom_delivery_fee (DECIMAL 10,2 NULL)
SET @has_col := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'custom_delivery_fee'
);

SET @sql := IF(@has_col = 0,
  'ALTER TABLE `customers` ADD COLUMN `custom_delivery_fee` DECIMAL(10,2) NULL AFTER `zip_code`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Opcional: observação para taxa personalizada
SET @has_note := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'custom_delivery_note'
);

SET @sql2 := IF(@has_note = 0,
  'ALTER TABLE `customers` ADD COLUMN `custom_delivery_note` VARCHAR(255) NULL AFTER `custom_delivery_fee`',
  'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;


