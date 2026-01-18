-- ============================================================================
-- MIGRAÇÃO MULTI-TENANT COMPLETA - Sistema Olika
-- ============================================================================
-- Data: 2024-12-18
-- EXECUTE APÓS O ROLLBACK (se necessário)
-- Este script faz tudo de uma vez: colunas, FKs, índices e UPDATE
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PARTE 1: ADICIONAR COLUNA client_id NAS TABELAS
-- ============================================================================

-- 1.1 CATEGORIES
ALTER TABLE `categories` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.2 SETTINGS
DROP TRIGGER IF EXISTS `trg_settings_singleton`;
ALTER TABLE `settings` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- Trigger para 1 settings por cliente
DELIMITER $$
CREATE TRIGGER `trg_settings_per_client` BEFORE INSERT ON `settings` FOR EACH ROW 
BEGIN
    IF (SELECT COUNT(*) FROM settings WHERE client_id = NEW.client_id) >= 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cada cliente só pode ter 1 registro em settings.';
    END IF;
END$$
DELIMITER ;

-- 1.3 COUPONS
ALTER TABLE `coupons` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.4 ALLERGENS
ALTER TABLE `allergens` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.5 INGREDIENTS
ALTER TABLE `ingredients` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.6 DELIVERY_FEES
ALTER TABLE `delivery_fees` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.7 DELIVERY_DISTANCE_PRICING
ALTER TABLE `delivery_distance_pricing` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.8 DELIVERY_RULES
ALTER TABLE `delivery_rules` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.9 DELIVERY_SCHEDULES
ALTER TABLE `delivery_schedules` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.10 LOYALTY_PROGRAMS
ALTER TABLE `loyalty_programs` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.11 PAYMENT_SETTINGS
ALTER TABLE `payment_settings` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.12 CUSTOMER_TAGS
ALTER TABLE `customer_tags` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.13 WHATSAPP_INSTANCES
ALTER TABLE `whatsapp_instances` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.14 WHATSAPP_SETTINGS
ALTER TABLE `whatsapp_settings` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.15 WHATSAPP_TEMPLATES
ALTER TABLE `whatsapp_templates` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.16 WHATSAPP_CAMPAIGNS
ALTER TABLE `whatsapp_campaigns` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.17 ORDER_STATUSES
ALTER TABLE `order_statuses` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.18 ANALYTICS_EVENTS
ALTER TABLE `analytics_events` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.19 AI_EXCEPTIONS
ALTER TABLE `ai_exceptions` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.20 USERS (client_id e role)
ALTER TABLE `users` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;
    
ALTER TABLE `users` 
    ADD COLUMN `role` ENUM('super_admin', 'admin', 'manager', 'operator') DEFAULT 'operator' AFTER `email`;

-- ============================================================================
-- PARTE 2: CRIAR FOREIGN KEYS
-- ============================================================================

-- customers -> clients
ALTER TABLE `customers` 
    ADD CONSTRAINT `fk_customers_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- orders -> clients
ALTER TABLE `orders` 
    ADD CONSTRAINT `fk_orders_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- products -> clients
ALTER TABLE `products` 
    ADD CONSTRAINT `fk_products_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- deployment_logs -> clients
ALTER TABLE `deployment_logs` 
    ADD CONSTRAINT `fk_deployment_logs_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- categories -> clients
ALTER TABLE `categories` 
    ADD CONSTRAINT `fk_categories_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- settings -> clients
ALTER TABLE `settings` 
    ADD CONSTRAINT `fk_settings_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- coupons -> clients
ALTER TABLE `coupons` 
    ADD CONSTRAINT `fk_coupons_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- allergens -> clients
ALTER TABLE `allergens` 
    ADD CONSTRAINT `fk_allergens_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ingredients -> clients
ALTER TABLE `ingredients` 
    ADD CONSTRAINT `fk_ingredients_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- delivery_fees -> clients
ALTER TABLE `delivery_fees` 
    ADD CONSTRAINT `fk_delivery_fees_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- delivery_distance_pricing -> clients
ALTER TABLE `delivery_distance_pricing` 
    ADD CONSTRAINT `fk_delivery_distance_pricing_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- delivery_rules -> clients
ALTER TABLE `delivery_rules` 
    ADD CONSTRAINT `fk_delivery_rules_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- delivery_schedules -> clients
ALTER TABLE `delivery_schedules` 
    ADD CONSTRAINT `fk_delivery_schedules_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- loyalty_programs -> clients
ALTER TABLE `loyalty_programs` 
    ADD CONSTRAINT `fk_loyalty_programs_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- payment_settings -> clients
ALTER TABLE `payment_settings` 
    ADD CONSTRAINT `fk_payment_settings_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- customer_tags -> clients
ALTER TABLE `customer_tags` 
    ADD CONSTRAINT `fk_customer_tags_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- whatsapp_instances -> clients
ALTER TABLE `whatsapp_instances` 
    ADD CONSTRAINT `fk_whatsapp_instances_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- whatsapp_settings -> clients
ALTER TABLE `whatsapp_settings` 
    ADD CONSTRAINT `fk_whatsapp_settings_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- whatsapp_templates -> clients
ALTER TABLE `whatsapp_templates` 
    ADD CONSTRAINT `fk_whatsapp_templates_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- whatsapp_campaigns -> clients
ALTER TABLE `whatsapp_campaigns` 
    ADD CONSTRAINT `fk_whatsapp_campaigns_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- order_statuses -> clients
ALTER TABLE `order_statuses` 
    ADD CONSTRAINT `fk_order_statuses_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- analytics_events -> clients
ALTER TABLE `analytics_events` 
    ADD CONSTRAINT `fk_analytics_events_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ai_exceptions -> clients
ALTER TABLE `ai_exceptions` 
    ADD CONSTRAINT `fk_ai_exceptions_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE;

-- users -> clients
ALTER TABLE `users` 
    ADD CONSTRAINT `fk_users_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================================
-- PARTE 3: CRIAR ÍNDICES
-- ============================================================================

CREATE INDEX `idx_categories_client` ON `categories`(`client_id`, `is_active`, `sort_order`);
CREATE INDEX `idx_products_client_active` ON `products`(`client_id`, `is_active`, `category_id`);
CREATE INDEX `idx_products_client_catalog` ON `products`(`client_id`, `show_in_catalog`, `is_available`);
CREATE INDEX `idx_customers_client_active` ON `customers`(`client_id`, `is_active`);
CREATE INDEX `idx_customers_client_phone` ON `customers`(`client_id`, `phone`);
CREATE INDEX `idx_orders_client_status` ON `orders`(`client_id`, `status`, `created_at`);
CREATE INDEX `idx_orders_client_payment` ON `orders`(`client_id`, `payment_status`);
CREATE INDEX `idx_orders_client_date` ON `orders`(`client_id`, `created_at`);
CREATE INDEX `idx_coupons_client_active` ON `coupons`(`client_id`, `is_active`, `expires_at`);
CREATE UNIQUE INDEX `idx_settings_client_unique` ON `settings`(`client_id`);
CREATE INDEX `idx_delivery_fees_client` ON `delivery_fees`(`client_id`, `is_active`);
CREATE INDEX `idx_delivery_distance_pricing_client` ON `delivery_distance_pricing`(`client_id`, `is_active`);
CREATE INDEX `idx_delivery_rules_client` ON `delivery_rules`(`client_id`, `is_active`);
CREATE INDEX `idx_delivery_schedules_client` ON `delivery_schedules`(`client_id`, `is_active`);
CREATE INDEX `idx_whatsapp_instances_client` ON `whatsapp_instances`(`client_id`, `status`);
CREATE INDEX `idx_whatsapp_campaigns_client` ON `whatsapp_campaigns`(`client_id`, `status`);
CREATE INDEX `idx_loyalty_programs_client` ON `loyalty_programs`(`client_id`, `is_active`);
CREATE INDEX `idx_customer_tags_client` ON `customer_tags`(`client_id`);
CREATE INDEX `idx_order_statuses_client` ON `order_statuses`(`client_id`, `active`);
CREATE INDEX `idx_analytics_client_date` ON `analytics_events`(`client_id`, `created_at`);
CREATE INDEX `idx_users_client` ON `users`(`client_id`, `role`);
CREATE INDEX `idx_users_email` ON `users`(`email`);

-- ============================================================================
-- PARTE 4: ATUALIZAR DADOS EXISTENTES (client_id = 1)
-- ============================================================================

SET @default_client_id = 1;

UPDATE `categories` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `settings` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `coupons` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `customers` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `products` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `orders` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `delivery_fees` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `delivery_distance_pricing` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `delivery_rules` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `delivery_schedules` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `loyalty_programs` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `payment_settings` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `customer_tags` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `whatsapp_instances` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `whatsapp_settings` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `whatsapp_templates` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `whatsapp_campaigns` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `order_statuses` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `analytics_events` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `ai_exceptions` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
UPDATE `users` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- MIGRAÇÃO COMPLETA!
-- ============================================================================

SELECT 'Migração multi-tenant concluída com sucesso!' AS status;
