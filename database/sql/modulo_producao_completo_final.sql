-- =============================================================================
-- MÓDULO DE PRODUÇÃO - SQL COMPLETO (VERSÃO FINAL COM VERIFICAÇÕES)
-- Data: 2026-01-25
-- Descrição: Cria todas as tabelas e expande ingredients para o módulo de produção
-- INSTRUÇÕES: Execute este arquivo no seu banco de dados MySQL
-- Esta versão verifica se as colunas já existem antes de adicionar
-- =============================================================================

SET @db_name = DATABASE();

-- =============================================================================
-- PARTE 1: Expandir tabela ingredients (com verificações)
-- =============================================================================

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

-- Adicionar foreign key para client_id (se não existir)
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

-- =============================================================================
-- PARTE 2: Criar tabelas do módulo de produção
-- =============================================================================

-- Tabela de receitas
CREATE TABLE IF NOT EXISTS `recipes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NULL,
  `total_weight` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Peso total em gramas',
  `hydration` DECIMAL(5,2) NOT NULL DEFAULT 70 COMMENT 'Porcentagem de hidratação',
  `levain` DECIMAL(5,2) NOT NULL DEFAULT 30 COMMENT 'Porcentagem de levain',
  `notes` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `use_milk_instead_of_water` TINYINT(1) NOT NULL DEFAULT 0,
  `is_fermented` TINYINT(1) NOT NULL DEFAULT 1,
  `is_bread` TINYINT(1) NOT NULL DEFAULT 1,
  `include_notes_in_print` TINYINT(1) NOT NULL DEFAULT 0,
  `packaging_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.5 COMMENT 'Custo de embalagem',
  `final_price` DECIMAL(10,2) NULL COMMENT 'Preço final de venda',
  `resale_price` DECIMAL(10,2) NULL COMMENT 'Preço de revenda',
  `cost` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Custo total calculado',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_recipes_client_active` (`client_id`, `is_active`),
  KEY `idx_recipes_category` (`category`),
  CONSTRAINT `fk_recipes_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de etapas das receitas
CREATE TABLE IF NOT EXISTS `recipe_steps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipe_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT 'Etapa 1',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_recipe_steps_recipe_sort` (`recipe_id`, `sort_order`),
  CONSTRAINT `fk_recipe_steps_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de ingredientes por etapa
CREATE TABLE IF NOT EXISTS `recipe_ingredients` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipe_step_id` BIGINT UNSIGNED NOT NULL,
  `ingredient_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'ingredient' COMMENT 'ingredient, levain, etc',
  `percentage` DECIMAL(8,2) NULL COMMENT 'Porcentagem em relação à farinha',
  `weight` DECIMAL(10,2) NULL COMMENT 'Peso em gramas (calculado ou fixo)',
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_recipe_ingredients_step_sort` (`recipe_step_id`, `sort_order`),
  KEY `idx_recipe_ingredients_ingredient` (`ingredient_id`),
  CONSTRAINT `fk_recipe_ingredients_step` FOREIGN KEY (`recipe_step_id`) REFERENCES `recipe_steps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_recipe_ingredients_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de registros de produção
CREATE TABLE IF NOT EXISTS `production_records` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NULL,
  `recipe_id` BIGINT UNSIGNED NOT NULL,
  `recipe_name` VARCHAR(255) NOT NULL COMMENT 'Nome da receita no momento da produção',
  `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Quantidade produzida',
  `weight` DECIMAL(10,2) NOT NULL COMMENT 'Peso unitário em gramas',
  `total_produced` DECIMAL(10,2) NOT NULL COMMENT 'Total produzido (quantity * weight)',
  `production_date` DATE NOT NULL,
  `observation` TEXT NULL,
  `cost` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Custo total da produção',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_production_records_client_date` (`client_id`, `production_date`),
  KEY `idx_production_records_recipe` (`recipe_id`),
  CONSTRAINT `fk_production_records_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_production_records_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de listas de produção
CREATE TABLE IF NOT EXISTS `production_lists` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NULL,
  `production_date` DATE NOT NULL,
  `status` ENUM('draft', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_production_lists_client_date` (`client_id`, `production_date`),
  KEY `idx_production_lists_status` (`status`),
  CONSTRAINT `fk_production_lists_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de itens das listas de produção
CREATE TABLE IF NOT EXISTS `production_list_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_list_id` BIGINT UNSIGNED NOT NULL,
  `recipe_id` BIGINT UNSIGNED NOT NULL,
  `recipe_name` VARCHAR(255) NOT NULL COMMENT 'Nome da receita no momento da adição',
  `quantity` INT NOT NULL DEFAULT 1,
  `weight` DECIMAL(10,2) NOT NULL COMMENT 'Peso unitário em gramas',
  `is_produced` TINYINT(1) NOT NULL DEFAULT 0,
  `produced_at` TIMESTAMP NULL DEFAULT NULL,
  `observation` TEXT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_production_list_items_list_sort` (`production_list_id`, `sort_order`),
  KEY `idx_production_list_items_recipe` (`recipe_id`),
  KEY `idx_production_list_items_produced` (`is_produced`),
  CONSTRAINT `fk_production_list_items_list` FOREIGN KEY (`production_list_id`) REFERENCES `production_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_production_list_items_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
