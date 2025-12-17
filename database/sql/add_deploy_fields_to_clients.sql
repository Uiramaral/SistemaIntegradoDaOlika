-- Adiciona campos de deploy na tabela clients
-- Executar apenas se os campos ainda não existirem

-- Adiciona coluna deploy_status (apenas se não existir)
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'deploy_status'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE clients ADD COLUMN deploy_status ENUM(\'pending\',\'in_progress\',\'completed\',\'failed\') DEFAULT \'pending\' AFTER instance_url',
    'SELECT "Coluna deploy_status já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar se instance_url já existe (caso não exista, criar)
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'instance_url'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE clients ADD COLUMN instance_url VARCHAR(255) NULL AFTER plan',
    'SELECT "Coluna instance_url já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


