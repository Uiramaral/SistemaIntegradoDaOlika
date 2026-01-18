-- ============================================================================
-- MIGRAÇÃO MULTI-TENANT - VERSÃO QUE PULA EXISTENTES
-- ============================================================================
-- Ignora erros de duplicação e continua executando
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PARTE 2: CRIAR FOREIGN KEYS (ignora se já existe)
-- ============================================================================

-- Procedure para adicionar FK ignorando erro de duplicação
DROP PROCEDURE IF EXISTS safe_add_fk;
DELIMITER //
CREATE PROCEDURE safe_add_fk(IN sql_stmt TEXT)
BEGIN
    DECLARE CONTINUE HANDLER FOR 1826 BEGIN END; -- Duplicate FK name
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END; -- Duplicate key name
    DECLARE CONTINUE HANDLER FOR 1005 BEGIN END; -- Can't create table (FK error)
    SET @sql = sql_stmt;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

CALL safe_add_fk("ALTER TABLE `customers` ADD CONSTRAINT `fk_customers_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `products` ADD CONSTRAINT `fk_products_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `deployment_logs` ADD CONSTRAINT `fk_deployment_logs_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `categories` ADD CONSTRAINT `fk_categories_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `settings` ADD CONSTRAINT `fk_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `coupons` ADD CONSTRAINT `fk_coupons_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `allergens` ADD CONSTRAINT `fk_allergens_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `ingredients` ADD CONSTRAINT `fk_ingredients_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `delivery_fees` ADD CONSTRAINT `fk_delivery_fees_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `delivery_distance_pricing` ADD CONSTRAINT `fk_delivery_distance_pricing_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `delivery_rules` ADD CONSTRAINT `fk_delivery_rules_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `delivery_schedules` ADD CONSTRAINT `fk_delivery_schedules_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `loyalty_programs` ADD CONSTRAINT `fk_loyalty_programs_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `payment_settings` ADD CONSTRAINT `fk_payment_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `customer_tags` ADD CONSTRAINT `fk_customer_tags_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `whatsapp_instances` ADD CONSTRAINT `fk_whatsapp_instances_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `whatsapp_settings` ADD CONSTRAINT `fk_whatsapp_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `whatsapp_templates` ADD CONSTRAINT `fk_whatsapp_templates_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `whatsapp_campaigns` ADD CONSTRAINT `fk_whatsapp_campaigns_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `order_statuses` ADD CONSTRAINT `fk_order_statuses_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `analytics_events` ADD CONSTRAINT `fk_analytics_events_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `ai_exceptions` ADD CONSTRAINT `fk_ai_exceptions_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
CALL safe_add_fk("ALTER TABLE `users` ADD CONSTRAINT `fk_users_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE SET NULL ON UPDATE CASCADE");

DROP PROCEDURE IF EXISTS safe_add_fk;

-- ============================================================================
-- PARTE 3: CRIAR ÍNDICES (ignora se já existe)
-- ============================================================================

DROP PROCEDURE IF EXISTS safe_add_index;
DELIMITER //
CREATE PROCEDURE safe_add_index(IN sql_stmt TEXT)
BEGIN
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END; -- Duplicate key name
    DECLARE CONTINUE HANDLER FOR 1068 BEGIN END; -- Multiple primary key
    SET @sql = sql_stmt;
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END //
DELIMITER ;

CALL safe_add_index("CREATE INDEX `idx_categories_client` ON `categories`(`client_id`, `is_active`, `sort_order`)");
CALL safe_add_index("CREATE INDEX `idx_products_client_active` ON `products`(`client_id`, `is_active`, `category_id`)");
CALL safe_add_index("CREATE INDEX `idx_products_client_catalog` ON `products`(`client_id`, `show_in_catalog`, `is_available`)");
CALL safe_add_index("CREATE INDEX `idx_customers_client_active` ON `customers`(`client_id`, `is_active`)");
CALL safe_add_index("CREATE INDEX `idx_customers_client_phone` ON `customers`(`client_id`, `phone`)");
CALL safe_add_index("CREATE INDEX `idx_orders_client_status` ON `orders`(`client_id`, `status`, `created_at`)");
CALL safe_add_index("CREATE INDEX `idx_orders_client_payment` ON `orders`(`client_id`, `payment_status`)");
CALL safe_add_index("CREATE INDEX `idx_orders_client_date` ON `orders`(`client_id`, `created_at`)");
CALL safe_add_index("CREATE INDEX `idx_coupons_client_active` ON `coupons`(`client_id`, `is_active`, `expires_at`)");
CALL safe_add_index("CREATE UNIQUE INDEX `idx_settings_client_unique` ON `settings`(`client_id`)");
CALL safe_add_index("CREATE INDEX `idx_delivery_fees_client` ON `delivery_fees`(`client_id`, `is_active`)");
CALL safe_add_index("CREATE INDEX `idx_delivery_distance_pricing_client` ON `delivery_distance_pricing`(`client_id`, `is_active`)");
CALL safe_add_index("CREATE INDEX `idx_delivery_rules_client` ON `delivery_rules`(`client_id`, `is_active`)");
CALL safe_add_index("CREATE INDEX `idx_delivery_schedules_client` ON `delivery_schedules`(`client_id`, `is_active`)");
CALL safe_add_index("CREATE INDEX `idx_whatsapp_instances_client` ON `whatsapp_instances`(`client_id`, `status`)");
CALL safe_add_index("CREATE INDEX `idx_whatsapp_campaigns_client` ON `whatsapp_campaigns`(`client_id`, `status`)");
CALL safe_add_index("CREATE INDEX `idx_loyalty_programs_client` ON `loyalty_programs`(`client_id`, `is_active`)");
CALL safe_add_index("CREATE INDEX `idx_customer_tags_client` ON `customer_tags`(`client_id`)");
CALL safe_add_index("CREATE INDEX `idx_order_statuses_client` ON `order_statuses`(`client_id`, `active`)");
CALL safe_add_index("CREATE INDEX `idx_analytics_client_date` ON `analytics_events`(`client_id`, `created_at`)");
CALL safe_add_index("CREATE INDEX `idx_users_client` ON `users`(`client_id`, `role`)");
CALL safe_add_index("CREATE INDEX `idx_users_email` ON `users`(`email`)");

DROP PROCEDURE IF EXISTS safe_add_index;

-- ============================================================================
-- PARTE 4: ATUALIZAR DADOS EXISTENTES (sempre executa)
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

SELECT 'Migração concluída! Todos os dados atualizados com client_id = 1' AS status;
