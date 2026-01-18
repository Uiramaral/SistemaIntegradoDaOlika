-- ==============================================
-- ADICIONAR COLUNA updated_at NA TABELA api_tokens
-- ==============================================

-- Verificar se a coluna updated_at já existe
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'api_tokens' 
    AND COLUMN_NAME = 'updated_at'
);

-- Adicionar coluna se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `api_tokens` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`',
    'SELECT "Coluna updated_at já existe na tabela api_tokens - OK!" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar resultado
SELECT 
    CASE 
        WHEN @col_exists > 0 THEN 'Coluna updated_at já existia'
        ELSE 'Coluna updated_at adicionada com sucesso!'
    END AS resultado;

