-- Adicionar campo show_in_catalog na tabela products
-- Permite controlar se o produto aparece no catálogo público ou apenas no PDV
-- true = aparece no catálogo (padrão)
-- false = só aparece no PDV

-- Verificar se o campo já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'products' 
    AND COLUMN_NAME = 'show_in_catalog'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `products` ADD COLUMN `show_in_catalog` tinyint(1) NOT NULL DEFAULT 1 COMMENT ''1 = Aparece no catálogo público, 0 = Apenas no PDV'' AFTER `is_active`;',
    'SELECT ''Campo show_in_catalog já existe na tabela products'' AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar produtos existentes para aparecer no catálogo por padrão (apenas se NULL)
UPDATE `products` SET `show_in_catalog` = 1 WHERE `show_in_catalog` IS NULL;

