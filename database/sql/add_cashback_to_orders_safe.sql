-- Adicionar campos de cashback na tabela orders (versão segura)
-- Verifica se as colunas existem antes de adicionar

-- Verificar e adicionar cashback_used
SET @dbname = DATABASE();
SET @tablename = 'orders';
SET @columnname = 'cashback_used';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT \'Cashback usado no pedido\' AFTER `discount_amount`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar cashback_earned
SET @columnname = 'cashback_earned';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN `', @columnname, '` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT \'Cashback gerado pelo pedido\' AFTER `cashback_used`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

