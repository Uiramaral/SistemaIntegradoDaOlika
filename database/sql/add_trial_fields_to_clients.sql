-- Adiciona campos de período de teste na tabela clients
-- Executar apenas se os campos ainda não existirem

-- Adiciona coluna is_trial (apenas se não existir)
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'is_trial'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE clients ADD COLUMN is_trial BOOLEAN DEFAULT FALSE AFTER active',
    'SELECT "Coluna is_trial já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adiciona coluna trial_started_at (apenas se não existir)
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'trial_started_at'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE clients ADD COLUMN trial_started_at TIMESTAMP NULL AFTER is_trial',
    'SELECT "Coluna trial_started_at já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adiciona coluna trial_ends_at (apenas se não existir)
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'trial_ends_at'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE clients ADD COLUMN trial_ends_at TIMESTAMP NULL AFTER trial_started_at',
    'SELECT "Coluna trial_ends_at já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

