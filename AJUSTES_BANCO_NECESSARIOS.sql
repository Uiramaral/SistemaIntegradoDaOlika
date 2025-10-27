-- ============================================
-- AJUSTES NECESSÁRIOS NO BANCO DE DADOS
-- Execute estes comandos no seu banco de produção
-- ============================================

-- 1. CRIAR TABELA cashback (NÃO EXISTE NO BANCO)
CREATE TABLE IF NOT EXISTS `cashback` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('credit','manual','bonus') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('pending','active','used','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `description` text COLLATE utf8mb4_unicode_ci,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashback_customer_id_index` (`customer_id`),
  CONSTRAINT `cashback_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADICIONAR campo slug em categories (se não existir)
SET @col_exists_slug = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'slug');
SET @sql = IF(@col_exists_slug = 0, 
  'ALTER TABLE `categories` ADD COLUMN `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`', 
  'SELECT "Campo slug já existe em categories" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Criar slugs para categorias existentes (se slug não existir)
UPDATE `categories` SET `slug` = LOWER(REPLACE(`name`, ' ', '-')) WHERE `slug` IS NULL;

-- 3. ADICIONAR campo sku em products (se não existir)
SET @col_exists_sku = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'sku');
SET @sql = IF(@col_exists_sku = 0, 
  'ALTER TABLE `products` ADD COLUMN `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`', 
  'SELECT "Campo sku já existe em products" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Criar SKUs automáticos para produtos existentes
UPDATE `products` SET `sku` = CONCAT('SKU-', LPAD(`id`, 5, '0')) WHERE `sku` IS NULL;

-- 4. ADICIONAR campos image e display_order em categories (se não existir)
SET @col_exists_image = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'image');
SET @sql = IF(@col_exists_image = 0, 
  'ALTER TABLE `categories` ADD COLUMN `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `slug`', 
  'SELECT "Campo image já existe" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists_display = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'display_order');
SET @sql = IF(@col_exists_display = 0, 
  'ALTER TABLE `categories` ADD COLUMN `display_order` int NOT NULL DEFAULT 0 AFTER `is_active`', 
  'SELECT "Campo display_order já existe" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. TABELA addresses - criar se não existir (usada no PDV)
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint UNSIGNED NOT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neighborhood` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_customer_id_foreign` (`customer_id`),
  CONSTRAINT `addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ADICIONAR address_id em orders (se não existir)
SET @col_exists_addr = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'address_id');
SET @sql = IF(@col_exists_addr = 0, 
  'ALTER TABLE `orders` ADD COLUMN `address_id` bigint UNSIGNED DEFAULT NULL AFTER `customer_id`, ADD KEY `orders_address_id_foreign` (`address_id`)', 
  'SELECT "Campo address_id já existe em orders" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. TABELA payments - criar se não existir
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json DEFAULT NULL,
  `pix_qr_base64` mediumtext COLLATE utf8mb4_unicode_ci,
  `pix_copia_cola` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABELA coupon_usages - criar se não existir
CREATE TABLE IF NOT EXISTS `coupon_usages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupon_usages_coupon_id_foreign` (`coupon_id`),
  KEY `coupon_usages_customer_id_foreign` (`customer_id`),
  KEY `coupon_usages_order_id_foreign` (`order_id`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABELAS para WhatsApp (se não existir)
CREATE TABLE IF NOT EXISTS `whatsapp_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `instance_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'olika_main',
  `api_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Olika Bot',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `whatsapp_templates_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABELAS para Order Status (se não existir)
CREATE TABLE IF NOT EXISTS `order_statuses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_final` tinyint(1) NOT NULL DEFAULT 0,
  `notify_customer` tinyint(1) NOT NULL DEFAULT 0,
  `notify_admin` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp_template_id` bigint UNSIGNED DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_statuses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_history_order_id_index` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. SEEDER de Status Padrão
INSERT INTO `order_statuses` (`code`, `name`, `is_final`, `notify_customer`, `notify_admin`, `active`, `created_at`, `updated_at`) VALUES
('pending', 'Pendente', 0, 1, 0, 1, NOW(), NOW()),
('confirmed', 'Confirmado', 0, 1, 1, 1, NOW(), NOW()),
('preparing', 'Preparando', 0, 1, 0, 1, NOW(), NOW()),
('ready', 'Pronto para Entrega', 0, 1, 1, 1, NOW(), NOW()),
('delivering', 'Entregando', 0, 1, 0, 1, NOW(), NOW()),
('delivered', 'Entregue', 1, 1, 1, 1, NOW(), NOW()),
('cancelled', 'Cancelado', 1, 1, 1, 1, NOW(), NOW()),
('paid', 'Pago', 0, 1, 1, 1, NOW(), NOW()),
('waiting_payment', 'Aguardando Pagamento', 0, 1, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- 12. Templates WhatsApp Padrão
INSERT INTO `whatsapp_templates` (`slug`, `content`, `active`, `created_at`, `updated_at`) VALUES
('order_pending', '🚚 Olá {nome}! Seu pedido #{pedido} foi recebido e está sendo processado. Aguardamos seu pagamento para iniciar a preparação!', 1, NOW(), NOW()),
('order_confirmed', '✅ Olá {nome}! Seu pedido #{pedido} foi confirmado. Estamos preparando seus itens com muito carinho! 🥖', 1, NOW(), NOW()),
('order_ready', '📦 Olá {nome}! Seu pedido #{pedido} está pronto e está sendo enviado! Chegando em breve!', 1, NOW(), NOW()),
('order_delivered', '🎉 Olá {nome}! Seu pedido #{pedido} foi entregue! Obrigada por confiar na Olika! Volte sempre!', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- ============================================
-- RESUMO DOS AJUSTES
-- ============================================
-- 1. ✅ Tabela cashback criada
-- 2. ✅ Campo slug adicionado em categories
-- 3. ✅ Campo sku adicionado em products
-- 4. ✅ Campos image e display_order em categories
-- 5. ✅ Tabela addresses criada
-- 6. ✅ Campo address_id em orders
-- 7. ✅ Tabela payments criada
-- 8. ✅ Tabela coupon_usages criada
-- 9. ✅ Tabelas whatsapp_settings e whatsapp_templates
-- 10. ✅ Tabelas order_statuses e order_status_history
-- 11. ✅ Dados iniciais (status e templates)
-- 12. ✅ Templates WhatsApp padrão

-- Execute este SQL no seu banco e o dashboard funcionará 100%!

