-- Adiciona coluna product_id à tabela recipes
-- Vincula receitas a produtos específicos
-- Data: 2026-01-25

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'recipes'
    AND COLUMN_NAME = 'product_id'
);

-- Adicionar coluna apenas se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `recipes` 
     ADD COLUMN `product_id` BIGINT UNSIGNED NULL 
     COMMENT ''Produto associado a esta receita''
     AFTER `client_id`,
     ADD KEY `idx_recipes_product` (`product_id`),
     ADD CONSTRAINT `fk_recipes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT ''Coluna product_id já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
