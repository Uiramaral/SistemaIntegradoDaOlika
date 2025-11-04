-- Adicionar campo total_debts na tabela customers para armazenar saldo de débitos pendentes
-- Similar ao campo loyalty_balance para cashback
-- NOTA: A tabela customer_debts já existe, este script apenas adiciona o campo na tabela customers

-- Verificar se o campo já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'customers' 
    AND COLUMN_NAME = 'total_debts'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `customers` ADD COLUMN `total_debts` decimal(10,2) NOT NULL DEFAULT ''0.00'' COMMENT ''Saldo total de débitos pendentes (pagamento postergado)'' AFTER `loyalty_balance`;',
    'SELECT ''Campo total_debts já existe na tabela customers'' AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se o índice já existe antes de criar
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'customers' 
    AND INDEX_NAME = 'idx_customers_total_debts'
);

SET @sql_idx = IF(@idx_exists = 0,
    'CREATE INDEX `idx_customers_total_debts` ON `customers` (`total_debts`);',
    'SELECT ''Índice idx_customers_total_debts já existe'' AS message;'
);

PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

