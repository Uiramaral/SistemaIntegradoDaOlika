-- ============================================
-- AJUSTES FINAIS - EXECUTE ISSO
-- ============================================
-- Este SQL cria SOMENTE o que não existe
-- Ignore os erros "campo duplicado" - é normal!

-- ============================================
-- PARTE 1: TABELAS
-- ============================================

-- 1. Tabela cashback
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

-- 2. Tabela addresses
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

-- 3. Tabela payments
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

-- 4. Tabela coupon_usages
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

-- 5. Tabela whatsapp_settings
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

-- 6. Tabela whatsapp_templates
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

-- 7. Tabela order_statuses
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

-- 8. Tabela order_status_history
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

-- ============================================
-- PARTE 2: CAMPOS (JÁ COMENTADO - Execute separadamente)
-- ============================================
-- Se você precisar adicionar esses campos, execute UM POR VEZ manualmente
-- Remove o comentário (--) do comando que você precisa

-- ALTER TABLE `categories` ADD COLUMN `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`;
-- ALTER TABLE `categories` ADD COLUMN `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `slug`;
-- ALTER TABLE `categories` ADD COLUMN `display_order` int NOT NULL DEFAULT 0 AFTER `is_active`;
-- ALTER TABLE `products` ADD COLUMN `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `name`;
-- ALTER TABLE `orders` ADD COLUMN `address_id` bigint UNSIGNED DEFAULT NULL AFTER `customer_id`;

-- ============================================
-- PARTE 3: DADOS INICIAIS
-- ============================================

-- Status de pedidos
INSERT INTO `order_statuses` (`code`, `name`, `is_final`, `notify_customer`, `notify_admin`, `active`) VALUES
('pending', 'Pendente', 0, 1, 0, 1),
('confirmed', 'Confirmado', 0, 1, 1, 1),
('preparing', 'Preparando', 0, 1, 0, 1),
('ready', 'Pronto para Entrega', 0, 1, 1, 1),
('delivering', 'Entregando', 0, 1, 0, 1),
('delivered', 'Entregue', 1, 1, 1, 1),
('cancelled', 'Cancelado', 1, 1, 1, 1),
('paid', 'Pago', 0, 1, 1, 1),
('waiting_payment', 'Aguardando Pagamento', 0, 1, 0, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Templates WhatsApp
INSERT INTO `whatsapp_templates` (`slug`, `content`, `active`) VALUES
('order_pending', '🚚 Olá {nome}! Seu pedido #{pedido} foi recebido e está sendo processado. Aguardamos seu pagamento para iniciar a preparação!', 1),
('order_confirmed', '✅ Olá {nome}! Seu pedido #{pedido} foi confirmado. Estamos preparando seus itens com muito carinho! 🥖', 1),
('order_ready', '📦 Olá {nome}! Seu pedido #{pedido} está pronto e está sendo enviado! Chegando em breve!', 1),
('order_delivered', '🎉 Olá {nome}! Seu pedido #{pedido} foi entregue! Obrigada por confiar na Olika! Volte sempre!', 1)
ON DUPLICATE KEY UPDATE `content` = VALUES(`content`);

-- Preencher slugs nas categories existentes (se o campo existir)
-- UPDATE `categories` SET `slug` = LOWER(REPLACE(`name`, ' ', '-')) WHERE `slug` IS NULL;

-- Preencher SKUs nos products existentes (se o campo existir)  
-- UPDATE `products` SET `sku` = CONCAT('SKU-', LPAD(`id`, 5, '0')) WHERE `sku` IS NULL;

