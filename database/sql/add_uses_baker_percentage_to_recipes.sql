-- Adiciona campo uses_baker_percentage à tabela recipes
-- Indica se a receita usa porcentagem de padeiro (baker's percentage)
-- ou se usa outro método de cálculo de ingredientes
-- Data: 2026-01-25

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'recipes'
    AND COLUMN_NAME = 'uses_baker_percentage'
);

-- Adicionar coluna apenas se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `recipes` 
     ADD COLUMN `uses_baker_percentage` TINYINT(1) NOT NULL DEFAULT 1 
     COMMENT ''1 = usa porcentagem de padeiro (farinha como base), 0 = outro método''
     AFTER `include_notes_in_print`',
    'SELECT ''Coluna uses_baker_percentage já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Resumo
SELECT 'Campo uses_baker_percentage adicionado à tabela recipes' AS info;
SELECT COUNT(*) AS total_recipes FROM recipes;
SELECT 
    uses_baker_percentage,
    COUNT(*) AS count
FROM recipes
GROUP BY uses_baker_percentage;
