-- SQL Parte 2: Verificar e adicionar foreign key
-- Execute este SQL depois da parte 1

SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order_items' 
    AND COLUMN_NAME = 'variant_id' 
    AND REFERENCED_TABLE_NAME = 'product_variants'
);

SET @sql_fk = IF(@fk_exists = 0,
    'ALTER TABLE `order_items` ADD CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL',
    'SELECT "Foreign key já existe" AS message'
);

PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

