-- Script para corrigir a estrutura da tabela orders
-- Adiciona colunas necessárias para o dashboard

-- Verificar se a coluna 'total' existe, se não, adicionar
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'orders' 
AND COLUMN_NAME = 'total';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN total DECIMAL(10,2) DEFAULT 0.00 AFTER status', 
    'SELECT "Coluna total já existe" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'status' existe, se não, adicionar
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'orders' 
AND COLUMN_NAME = 'status';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN status VARCHAR(50) DEFAULT "pending" AFTER total', 
    'SELECT "Coluna status já existe" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se a coluna 'customer_id' existe, se não, adicionar
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'orders' 
AND COLUMN_NAME = 'customer_id';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE orders ADD COLUMN customer_id INT UNSIGNED AFTER id', 
    'SELECT "Coluna customer_id já existe" as message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar registros existentes com valores padrão se necessário
UPDATE orders SET total = 0.00 WHERE total IS NULL;
UPDATE orders SET status = 'pending' WHERE status IS NULL OR status = '';

-- Verificar estrutura final da tabela
DESCRIBE orders;
