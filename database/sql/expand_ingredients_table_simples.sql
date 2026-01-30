-- Expandir tabela ingredients com campos de produção
-- Data: 2026-01-25
-- Versão simplificada - Execute este SQL diretamente
-- Se alguma coluna já existir, ignore o erro e continue

-- client_id
ALTER TABLE `ingredients` 
ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- weight
ALTER TABLE `ingredients` 
ADD COLUMN `weight` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Peso padrão em gramas' AFTER `slug`;

-- percentage
ALTER TABLE `ingredients` 
ADD COLUMN `percentage` DECIMAL(8,2) NULL COMMENT 'Porcentagem padrão em receitas' AFTER `weight`;

-- is_flour
ALTER TABLE `ingredients` 
ADD COLUMN `is_flour` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se é farinha' AFTER `percentage`;

-- has_hydration
ALTER TABLE `ingredients` 
ADD COLUMN `has_hydration` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se tem hidratação' AFTER `is_flour`;

-- hydration_percentage
ALTER TABLE `ingredients` 
ADD COLUMN `hydration_percentage` DECIMAL(5,2) NULL DEFAULT 0 COMMENT 'Porcentagem de hidratação' AFTER `has_hydration`;

-- category
ALTER TABLE `ingredients` 
ADD COLUMN `category` VARCHAR(50) NULL COMMENT 'Categoria: farinha, outro, etc' AFTER `hydration_percentage`;

-- package_weight
ALTER TABLE `ingredients` 
ADD COLUMN `package_weight` DECIMAL(10,2) NULL COMMENT 'Peso da embalagem em gramas' AFTER `category`;

-- cost
ALTER TABLE `ingredients` 
ADD COLUMN `cost` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Custo por unidade/embalagem' AFTER `package_weight`;

-- cost_history
ALTER TABLE `ingredients` 
ADD COLUMN `cost_history` JSON NULL COMMENT 'Histórico de custos' AFTER `cost`;

-- unit
ALTER TABLE `ingredients` 
ADD COLUMN `unit` VARCHAR(20) NOT NULL DEFAULT 'g' COMMENT 'Unidade: g, kg, ml, l, un' AFTER `cost_history`;

-- stock
ALTER TABLE `ingredients` 
ADD COLUMN `stock` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Estoque atual' AFTER `unit`;

-- min_stock
ALTER TABLE `ingredients` 
ADD COLUMN `min_stock` DECIMAL(10,2) NULL DEFAULT 0 COMMENT 'Estoque mínimo' AFTER `stock`;

-- is_active
ALTER TABLE `ingredients` 
ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `min_stock`;

-- created_at
ALTER TABLE `ingredients` 
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_active`;

-- updated_at
ALTER TABLE `ingredients` 
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;

-- Adicionar foreign key para client_id (se não existir)
ALTER TABLE `ingredients` 
ADD CONSTRAINT `ingredients_client_id_foreign` 
FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
