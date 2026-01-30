-- Expandir tabela ingredients com campos de produção
-- Data: 2026-01-25
-- Execute este SQL apenas se as colunas ainda não existirem

-- Verificar e adicionar colunas se não existirem
SET @db_name = DATABASE();

-- client_id
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'client_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;',
    'SELECT "Coluna client_id já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- weight
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'weight'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `weight` DECIMAL(10,2) NULL DEFAULT 0 COMMENT ''Peso padrão em gramas'' AFTER `slug`;',
    'SELECT "Coluna weight já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- percentage
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'percentage'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `percentage` DECIMAL(8,2) NULL COMMENT ''Porcentagem padrão em receitas'' AFTER `weight`;',
    'SELECT "Coluna percentage já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- is_flour
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'is_flour'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `is_flour` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Se é farinha'' AFTER `percentage`;',
    'SELECT "Coluna is_flour já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- has_hydration
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'has_hydration'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `has_hydration` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Se tem hidratação'' AFTER `is_flour`;',
    'SELECT "Coluna has_hydration já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- hydration_percentage
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'hydration_percentage'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `hydration_percentage` DECIMAL(5,2) NULL DEFAULT 0 COMMENT ''Porcentagem de hidratação'' AFTER `has_hydration`;',
    'SELECT "Coluna hydration_percentage já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- category
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'category'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `category` VARCHAR(50) NULL COMMENT ''Categoria: farinha, outro, etc'' AFTER `hydration_percentage`;',
    'SELECT "Coluna category já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- package_weight
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'package_weight'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `package_weight` DECIMAL(10,2) NULL COMMENT ''Peso da embalagem em gramas'' AFTER `category`;',
    'SELECT "Coluna package_weight já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- cost
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'cost'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `cost` DECIMAL(10,2) NULL DEFAULT 0 COMMENT ''Custo por unidade/embalagem'' AFTER `package_weight`;',
    'SELECT "Coluna cost já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- cost_history
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'cost_history'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `cost_history` JSON NULL COMMENT ''Histórico de custos'' AFTER `cost`;',
    'SELECT "Coluna cost_history já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- unit
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'unit'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `unit` VARCHAR(20) NOT NULL DEFAULT ''g'' COMMENT ''Unidade: g, kg, ml, l, un'' AFTER `cost_history`;',
    'SELECT "Coluna unit já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- stock
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'stock'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `stock` DECIMAL(10,2) NULL DEFAULT 0 COMMENT ''Estoque atual'' AFTER `unit`;',
    'SELECT "Coluna stock já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- min_stock
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'min_stock'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `min_stock` DECIMAL(10,2) NULL DEFAULT 0 COMMENT ''Estoque mínimo'' AFTER `stock`;',
    'SELECT "Coluna min_stock já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- is_active
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'is_active'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `min_stock`;',
    'SELECT "Coluna is_active já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- created_at
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'created_at'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_active`;',
    'SELECT "Coluna created_at já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- updated_at
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ingredients' 
    AND COLUMN_NAME = 'updated_at'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `ingredients` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;',
    'SELECT "Coluna updated_at já existe" AS message;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar foreign key para client_id se não existir
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'ingredients' 
    AND CONSTRAINT_NAME = 'ingredients_client_id_foreign'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `ingredients` ADD CONSTRAINT `ingredients_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;',
    'SELECT "Foreign key já existe" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
