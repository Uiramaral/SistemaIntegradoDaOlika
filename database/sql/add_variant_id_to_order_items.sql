-- Adicionar coluna variant_id à tabela order_items
-- Execute apenas se a coluna ainda não existir

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order_items' 
    AND COLUMN_NAME = 'variant_id'
);

-- Adicionar coluna se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `order_items` 
     ADD COLUMN `variant_id` BIGINT UNSIGNED NULL 
     AFTER `product_id`',
    'SELECT "Coluna variant_id já existe" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar foreign key se a coluna foi criada
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order_items' 
    AND COLUMN_NAME = 'variant_id' 
    AND REFERENCED_TABLE_NAME = 'product_variants'
);

SET @sql_fk = IF(@fk_exists = 0 AND @col_exists = 0,
    'ALTER TABLE `order_items` 
     ADD CONSTRAINT `order_items_variant_id_foreign` 
     FOREIGN KEY (`variant_id`) 
     REFERENCES `product_variants` (`id`) 
     ON DELETE SET NULL',
    'SELECT "Foreign key já existe ou coluna não foi criada" AS message'
);

PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

