-- ============================================================================
-- MIGRAÇÃO MULTI-TENANT (SaaS) - Sistema Olika
-- ============================================================================
-- Data: 2024-12-18
-- Objetivo: Adicionar isolamento por client_id em todas as tabelas necessárias
-- ============================================================================

-- IMPORTANTE: Execute este script em uma janela de manutenção
-- Faça backup completo antes de executar!

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PARTE 1: ADICIONAR COLUNA client_id NAS TABELAS QUE FALTAM
-- ============================================================================

-- 1.1 CATEGORIES (CRÍTICO - categorias por cliente)
ALTER TABLE `categories` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.2 SETTINGS (CRÍTICO - configurações por cliente)
-- Primeiro remover o trigger que impede múltiplas linhas
DROP TRIGGER IF EXISTS `trg_settings_singleton`;

ALTER TABLE `settings` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- Recriar trigger para permitir 1 settings POR CLIENTE
DELIMITER $$
CREATE TRIGGER `trg_settings_per_client` BEFORE INSERT ON `settings` FOR EACH ROW 
BEGIN
    IF (SELECT COUNT(*) FROM settings WHERE client_id = NEW.client_id) >= 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cada cliente só pode ter 1 registro em settings.';
    END IF;
END$$
DELIMITER ;

-- 1.3 COUPONS (CRÍTICO - cupons por cliente)
ALTER TABLE `coupons` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.4 ALLERGENS (pode ser global ou por cliente - opcional)
ALTER TABLE `allergens` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.5 INGREDIENTS (pode ser global ou por cliente - opcional)
ALTER TABLE `ingredients` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.6 DELIVERY_FEES (taxas de entrega por cliente)
ALTER TABLE `delivery_fees` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.7 DELIVERY_DISTANCE_PRICING (preços por distância por cliente)
ALTER TABLE `delivery_distance_pricing` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.8 DELIVERY_RULES (regras de entrega por cliente)
ALTER TABLE `delivery_rules` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.9 DELIVERY_SCHEDULES (horários de entrega por cliente)
ALTER TABLE `delivery_schedules` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.10 LOYALTY_PROGRAMS (programas de fidelidade por cliente)
ALTER TABLE `loyalty_programs` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.11 PAYMENT_SETTINGS (configurações de pagamento por cliente)
ALTER TABLE `payment_settings` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.12 CUSTOMER_TAGS (tags de clientes por client)
ALTER TABLE `customer_tags` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.13 WHATSAPP_INSTANCES (instâncias WhatsApp por cliente)
ALTER TABLE `whatsapp_instances` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.14 WHATSAPP_SETTINGS (configurações WhatsApp por cliente)
ALTER TABLE `whatsapp_settings` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.15 WHATSAPP_TEMPLATES (templates WhatsApp por cliente)
ALTER TABLE `whatsapp_templates` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.16 WHATSAPP_CAMPAIGNS (campanhas por cliente)
ALTER TABLE `whatsapp_campaigns` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.17 ORDER_STATUSES (status customizados por cliente)
ALTER TABLE `order_statuses` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.18 ANALYTICS_EVENTS (eventos por cliente)
ALTER TABLE `analytics_events` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.19 AI_EXCEPTIONS (exceções de IA por cliente)
ALTER TABLE `ai_exceptions` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 1.20 USERS (admins/funcionários por cliente)
ALTER TABLE `users` 
    ADD COLUMN `client_id` BIGINT UNSIGNED NULL AFTER `id`,
    ADD COLUMN `role` ENUM('super_admin', 'admin', 'manager', 'operator') DEFAULT 'operator' AFTER `email`;

-- ============================================================================
-- PARTE 2: CRIAR FOREIGN KEYS
-- ============================================================================

-- 2.1 FK para tabelas que JÁ TÊM client_id mas sem FK

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

-- 2.2 FK para tabelas que ACABAMOS DE ADICIONAR client_id

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

-- users -> clients (admins/funcionarios por cliente)
ALTER TABLE `users` 
    ADD CONSTRAINT `fk_users_client` 
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================================
-- PARTE 3: CRIAR ÍNDICES COMPOSTOS PARA PERFORMANCE
-- ============================================================================

-- 3.1 Índices para queries frequentes por client_id

-- Categories
CREATE INDEX `idx_categories_client` ON `categories`(`client_id`, `is_active`, `sort_order`);

-- Products
CREATE INDEX `idx_products_client_active` ON `products`(`client_id`, `is_active`, `category_id`);
CREATE INDEX `idx_products_client_catalog` ON `products`(`client_id`, `show_in_catalog`, `is_available`);

-- Customers
CREATE INDEX `idx_customers_client_active` ON `customers`(`client_id`, `is_active`);
CREATE INDEX `idx_customers_client_phone` ON `customers`(`client_id`, `phone`);

-- Orders
CREATE INDEX `idx_orders_client_status` ON `orders`(`client_id`, `status`, `created_at`);
CREATE INDEX `idx_orders_client_payment` ON `orders`(`client_id`, `payment_status`);
CREATE INDEX `idx_orders_client_date` ON `orders`(`client_id`, `created_at`);

-- Coupons
CREATE INDEX `idx_coupons_client_active` ON `coupons`(`client_id`, `is_active`, `expires_at`);

-- Settings
CREATE UNIQUE INDEX `idx_settings_client_unique` ON `settings`(`client_id`);

-- Delivery
CREATE INDEX `idx_delivery_fees_client` ON `delivery_fees`(`client_id`, `is_active`);
CREATE INDEX `idx_delivery_distance_pricing_client` ON `delivery_distance_pricing`(`client_id`, `is_active`);
CREATE INDEX `idx_delivery_rules_client` ON `delivery_rules`(`client_id`, `is_active`);
CREATE INDEX `idx_delivery_schedules_client` ON `delivery_schedules`(`client_id`, `is_active`);

-- WhatsApp
CREATE INDEX `idx_whatsapp_instances_client` ON `whatsapp_instances`(`client_id`, `status`);
CREATE INDEX `idx_whatsapp_campaigns_client` ON `whatsapp_campaigns`(`client_id`, `status`);

-- Loyalty
CREATE INDEX `idx_loyalty_programs_client` ON `loyalty_programs`(`client_id`, `is_active`);

-- Customer Tags
CREATE INDEX `idx_customer_tags_client` ON `customer_tags`(`client_id`);

-- Order Statuses
CREATE INDEX `idx_order_statuses_client` ON `order_statuses`(`client_id`, `active`);

-- Analytics
CREATE INDEX `idx_analytics_client_date` ON `analytics_events`(`client_id`, `created_at`);

-- Users
CREATE INDEX `idx_users_client` ON `users`(`client_id`, `role`);
CREATE INDEX `idx_users_email` ON `users`(`email`);

-- ============================================================================
-- PARTE 4: ATUALIZAR DADOS EXISTENTES (para migração)
-- ============================================================================

-- Se você já tem um client_id padrão (ex: 1), atualize os registros existentes
-- AJUSTE O VALOR 1 PARA O ID DO SEU CLIENTE PRINCIPAL

-- SET @default_client_id = 1;

-- UPDATE `categories` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `settings` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `coupons` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `customers` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `products` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `orders` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `delivery_fees` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `delivery_distance_pricing` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `delivery_rules` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `delivery_schedules` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `loyalty_programs` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `payment_settings` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `customer_tags` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `whatsapp_instances` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `whatsapp_settings` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `whatsapp_templates` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `whatsapp_campaigns` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `order_statuses` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `analytics_events` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `ai_exceptions` SET `client_id` = @default_client_id WHERE `client_id` IS NULL;
-- UPDATE `users` SET `client_id` = @default_client_id WHERE `client_id` IS NULL AND `role` != 'super_admin';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIM DA MIGRAÇÃO
-- ============================================================================
-- Após executar:
-- 1. Verifique se todas as FKs foram criadas: SHOW CREATE TABLE nome_tabela;
-- 2. Descomente e execute a PARTE 4 para migrar dados existentes
-- 3. Atualize os Models do Laravel (próximo passo)
-- ============================================================================
