-- Adiciona campo uses_baker_percentage à tabela products
-- Indica se o produto usa porcentagem de padeiro (baker's percentage)
-- ou se usa outro método de cálculo de ingredientes
-- Data: 2026-01-25

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'products'
    AND COLUMN_NAME = 'uses_baker_percentage'
);

-- Adicionar coluna apenas se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `products` 
     ADD COLUMN `uses_baker_percentage` TINYINT(1) NOT NULL DEFAULT 1 
     COMMENT ''1 = usa porcentagem de padeiro (farinha como base), 0 = outro método''
     AFTER `weight_grams`',
    'SELECT ''Coluna uses_baker_percentage já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Resumo
SELECT 'Campo uses_baker_percentage adicionado à tabela products' AS info;
SELECT COUNT(*) AS total_products FROM products;
SELECT 
    uses_baker_percentage,
    COUNT(*) AS count
FROM products
GROUP BY uses_baker_percentage;
