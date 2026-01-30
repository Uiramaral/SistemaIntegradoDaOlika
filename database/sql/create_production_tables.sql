-- Criar tabelas do módulo de produção
-- Data: 2026-01-25

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
