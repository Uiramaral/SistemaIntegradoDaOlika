-- ============================================
-- CRIAR TABELAS DE ALÉRGENICOS E IMAGENS
-- ============================================

-- 1. Verificar se tabela allergens existe (já existe no sistema)
-- Não criamos, apenas verificamos
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'allergens') > 0,
    'SELECT "Tabela allergens já existe - OK!" AS message;',
    'SELECT "ATENÇÃO: Tabela allergens NÃO encontrada! Execute o arquivo allergens.sql primeiro." AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Criar tabela product_allergen se não existir
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'product_allergen') > 0,
    'SELECT "Tabela product_allergen já existe - OK!" AS message;',
    'CREATE TABLE `product_allergen` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `product_id` BIGINT UNSIGNED NOT NULL,
        `allergen_id` BIGINT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP NULL,
        `updated_at` TIMESTAMP NULL,
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
        FOREIGN KEY (`allergen_id`) REFERENCES `allergens` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `product_allergen_unique` (`product_id`, `allergen_id`)
    );'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Criar tabela product_images se não existir
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'product_images') > 0,
    'SELECT "Tabela product_images já existe - OK!" AS message;',
    'CREATE TABLE `product_images` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `product_id` BIGINT UNSIGNED NOT NULL,
        `path` VARCHAR(255) NOT NULL,
        `is_primary` BOOLEAN NOT NULL DEFAULT FALSE,
        `sort_order` INT NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NULL,
        `updated_at` TIMESTAMP NULL,
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
        INDEX `product_images_product_primary_idx` (`product_id`, `is_primary`),
        INDEX `product_images_sort_idx` (`sort_order`)
    );'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Adicionar campos faltantes na tabela products se não existirem
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'sku') = 0,
    'ALTER TABLE `products` ADD COLUMN `sku` VARCHAR(100) NULL AFTER `name`;',
    'SELECT "Coluna sku já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'stock') = 0,
    'ALTER TABLE `products` ADD COLUMN `stock` INT NULL DEFAULT 0 AFTER `price`;',
    'SELECT "Coluna stock já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'gluten_free') = 0,
    'ALTER TABLE `products` ADD COLUMN `gluten_free` BOOLEAN NOT NULL DEFAULT FALSE AFTER `stock`;',
    'SELECT "Coluna gluten_free já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'contamination_risk') = 0,
    'ALTER TABLE `products` ADD COLUMN `contamination_risk` BOOLEAN NOT NULL DEFAULT FALSE AFTER `gluten_free`;',
    'SELECT "Coluna contamination_risk já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'cover_image') = 0,
    'ALTER TABLE `products` ADD COLUMN `cover_image` VARCHAR(500) NULL AFTER `contamination_risk`;',
    'SELECT "Coluna cover_image já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'label_description') = 0,
    'ALTER TABLE `products` ADD COLUMN `label_description` TEXT NULL AFTER `description`;',
    'SELECT "Coluna label_description já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'seo_title') = 0,
    'ALTER TABLE `products` ADD COLUMN `seo_title` VARCHAR(255) NULL AFTER `label_description`;',
    'SELECT "Coluna seo_title já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'products' 
     AND COLUMN_NAME = 'seo_description') = 0,
    'ALTER TABLE `products` ADD COLUMN `seo_description` TEXT NULL AFTER `seo_title`;',
    'SELECT "Coluna seo_description já existe - OK!" AS message;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Verificar se existem alérgenicos (a tabela já tem dados do allergens.sql)
-- Não inserimos novos, apenas verificamos
SELECT COUNT(*) as total_allergens_existentes FROM `allergens`;

-- Verificar resultado
SELECT 'Tabelas de alérgenicos e imagens criadas/verificadas!' AS status;
SELECT COUNT(*) as total_allergens FROM `allergens`;
SELECT COUNT(*) as total_product_images FROM `product_images`;

