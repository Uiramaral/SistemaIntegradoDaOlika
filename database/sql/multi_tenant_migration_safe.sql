-- ============================================================================
-- MIGRAÇÃO MULTI-TENANT - VERSÃO SEGURA (IDEMPOTENTE)
-- ============================================================================
-- Esta versão verifica se cada coluna/FK/índice já existe antes de criar
-- Pode ser executada múltiplas vezes sem erros
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PROCEDIMENTO AUXILIAR PARA ADICIONAR COLUNAS DE FORMA SEGURA
-- ============================================================================

DROP PROCEDURE IF EXISTS add_column_if_not_exists;
DELIMITER //
CREATE PROCEDURE add_column_if_not_exists(
    IN table_name VARCHAR(255),
    IN column_name VARCHAR(255),
    IN column_definition VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = table_name 
        AND COLUMN_NAME = column_name
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', table_name, '` ADD COLUMN `', column_name, '` ', column_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- PROCEDIMENTO PARA ADICIONAR FK DE FORMA SEGURA
-- ============================================================================

DROP PROCEDURE IF EXISTS add_fk_if_not_exists;
DELIMITER //
CREATE PROCEDURE add_fk_if_not_exists(
    IN table_name VARCHAR(255),
    IN fk_name VARCHAR(255),
    IN column_name VARCHAR(255),
    IN ref_table VARCHAR(255),
    IN ref_column VARCHAR(255),
    IN on_delete_action VARCHAR(50)
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = table_name 
        AND CONSTRAINT_NAME = fk_name
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        SET @sql = CONCAT(
            'ALTER TABLE `', table_name, '` ADD CONSTRAINT `', fk_name, 
            '` FOREIGN KEY (`', column_name, '`) REFERENCES `', ref_table, 
            '`(`', ref_column, '`) ON DELETE ', on_delete_action, ' ON UPDATE CASCADE'
        );
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- PROCEDIMENTO PARA ADICIONAR ÍNDICE DE FORMA SEGURA
-- ============================================================================

DROP PROCEDURE IF EXISTS add_index_if_not_exists;
DELIMITER //
CREATE PROCEDURE add_index_if_not_exists(
    IN table_name VARCHAR(255),
    IN index_name VARCHAR(255),
    IN index_columns VARCHAR(500)
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = table_name 
        AND INDEX_NAME = index_name
    ) THEN
        SET @sql = CONCAT('CREATE INDEX `', index_name, '` ON `', table_name, '`(', index_columns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- PARTE 1: ADICIONAR COLUNA client_id NAS TABELAS QUE FALTAM
-- ============================================================================

CALL add_column_if_not_exists('categories', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('settings', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('coupons', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('allergens', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('ingredients', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('delivery_fees', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('delivery_distance_pricing', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('delivery_rules', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('delivery_schedules', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('loyalty_programs', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('payment_settings', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('customer_tags', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('whatsapp_instances', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('whatsapp_settings', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('whatsapp_templates', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('whatsapp_campaigns', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('order_statuses', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('analytics_events', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');
CALL add_column_if_not_exists('ai_exceptions', 'client_id', 'BIGINT UNSIGNED NULL AFTER `id`');

-- Users: precisa verificar se a coluna id existe (pode ser diferente em algumas instalações)
-- Adicionar client_id e role separadamente com tratamento de erro
SET @has_users_client_id = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'client_id'
);

SET @sql_users_client = IF(@has_users_client_id = 0, 
    'ALTER TABLE `users` ADD COLUMN `client_id` BIGINT UNSIGNED NULL', 
    'SELECT 1'
);
PREPARE stmt_users_client FROM @sql_users_client;
EXECUTE stmt_users_client;
DEALLOCATE PREPARE stmt_users_client;

SET @has_users_role = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'role'
);

SET @sql_users_role = IF(@has_users_role = 0, 
    "ALTER TABLE `users` ADD COLUMN `role` ENUM('super_admin', 'admin', 'manager', 'operator') DEFAULT 'operator'", 
    'SELECT 1'
);
PREPARE stmt_users_role FROM @sql_users_role;
EXECUTE stmt_users_role;
DEALLOCATE PREPARE stmt_users_role;

-- ============================================================================
-- PARTE 2: CRIAR FOREIGN KEYS
-- ============================================================================

CALL add_fk_if_not_exists('customers', 'fk_customers_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('orders', 'fk_orders_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('products', 'fk_products_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('deployment_logs', 'fk_deployment_logs_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('categories', 'fk_categories_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('settings', 'fk_settings_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('coupons', 'fk_coupons_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('allergens', 'fk_allergens_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('ingredients', 'fk_ingredients_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('delivery_fees', 'fk_delivery_fees_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('delivery_distance_pricing', 'fk_delivery_distance_pricing_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('delivery_rules', 'fk_delivery_rules_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('delivery_schedules', 'fk_delivery_schedules_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('loyalty_programs', 'fk_loyalty_programs_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('payment_settings', 'fk_payment_settings_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('customer_tags', 'fk_customer_tags_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('whatsapp_instances', 'fk_whatsapp_instances_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('whatsapp_settings', 'fk_whatsapp_settings_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('whatsapp_templates', 'fk_whatsapp_templates_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('whatsapp_campaigns', 'fk_whatsapp_campaigns_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('order_statuses', 'fk_order_statuses_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('analytics_events', 'fk_analytics_events_client', 'client_id', 'clients', 'id', 'SET NULL');
CALL add_fk_if_not_exists('ai_exceptions', 'fk_ai_exceptions_client', 'client_id', 'clients', 'id', 'CASCADE');
CALL add_fk_if_not_exists('users', 'fk_users_client', 'client_id', 'clients', 'id', 'SET NULL');

-- ============================================================================
-- PARTE 3: CRIAR ÍNDICES COMPOSTOS PARA PERFORMANCE
-- ============================================================================

CALL add_index_if_not_exists('categories', 'idx_categories_client', '`client_id`, `is_active`, `sort_order`');
CALL add_index_if_not_exists('products', 'idx_products_client_active', '`client_id`, `is_active`, `category_id`');
CALL add_index_if_not_exists('products', 'idx_products_client_catalog', '`client_id`, `show_in_catalog`, `is_available`');
CALL add_index_if_not_exists('customers', 'idx_customers_client_active', '`client_id`, `is_active`');
CALL add_index_if_not_exists('customers', 'idx_customers_client_phone', '`client_id`, `phone`');
CALL add_index_if_not_exists('orders', 'idx_orders_client_status', '`client_id`, `status`, `created_at`');
CALL add_index_if_not_exists('orders', 'idx_orders_client_payment', '`client_id`, `payment_status`');
CALL add_index_if_not_exists('orders', 'idx_orders_client_date', '`client_id`, `created_at`');
CALL add_index_if_not_exists('coupons', 'idx_coupons_client_active', '`client_id`, `is_active`, `expires_at`');
CALL add_index_if_not_exists('delivery_fees', 'idx_delivery_fees_client', '`client_id`, `is_active`');
CALL add_index_if_not_exists('delivery_distance_pricing', 'idx_delivery_distance_pricing_client', '`client_id`, `is_active`');
CALL add_index_if_not_exists('delivery_rules', 'idx_delivery_rules_client', '`client_id`, `is_active`');
CALL add_index_if_not_exists('delivery_schedules', 'idx_delivery_schedules_client', '`client_id`, `is_active`');
CALL add_index_if_not_exists('whatsapp_instances', 'idx_whatsapp_instances_client', '`client_id`, `status`');
CALL add_index_if_not_exists('whatsapp_campaigns', 'idx_whatsapp_campaigns_client', '`client_id`, `status`');
CALL add_index_if_not_exists('loyalty_programs', 'idx_loyalty_programs_client', '`client_id`, `is_active`');
CALL add_index_if_not_exists('customer_tags', 'idx_customer_tags_client', '`client_id`');
CALL add_index_if_not_exists('order_statuses', 'idx_order_statuses_client', '`client_id`, `active`');
CALL add_index_if_not_exists('analytics_events', 'idx_analytics_client_date', '`client_id`, `created_at`');
CALL add_index_if_not_exists('users', 'idx_users_client', '`client_id`, `role`');
CALL add_index_if_not_exists('users', 'idx_users_email', '`email`');

-- Índice único para settings por client
-- (tratado separadamente porque é UNIQUE)
DROP PROCEDURE IF EXISTS add_unique_index_if_not_exists;
DELIMITER //
CREATE PROCEDURE add_unique_index_if_not_exists(
    IN table_name VARCHAR(255),
    IN index_name VARCHAR(255),
    IN index_columns VARCHAR(500)
)
BEGIN
    IF NOT EXISTS (
        SELECT * FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = table_name 
        AND INDEX_NAME = index_name
    ) THEN
        SET @sql = CONCAT('CREATE UNIQUE INDEX `', index_name, '` ON `', table_name, '`(', index_columns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

CALL add_unique_index_if_not_exists('settings', 'idx_settings_client_unique', '`client_id`');

-- ============================================================================
-- PARTE 4: ATUALIZAR DADOS EXISTENTES (para migração)
-- ============================================================================

-- Descomente as linhas abaixo para atribuir client_id=1 aos registros existentes
-- AJUSTE O VALOR 1 PARA O ID DO SEU CLIENTE PRINCIPAL (Olika)

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
UPDATE `users` SET `client_id` = @default_client_id WHERE `client_id` IS NULL AND (`role` IS NULL OR `role` != 'super_admin');

-- Definir seu usuário como super_admin
-- UPDATE `users` SET `role` = 'super_admin', `client_id` = 1 WHERE `email` = 'seu_email@olika.com.br';

-- ============================================================================
-- LIMPEZA - Remover procedures auxiliares
-- ============================================================================

DROP PROCEDURE IF EXISTS add_column_if_not_exists;
DROP PROCEDURE IF EXISTS add_fk_if_not_exists;
DROP PROCEDURE IF EXISTS add_index_if_not_exists;
DROP PROCEDURE IF EXISTS add_unique_index_if_not_exists;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIM DA MIGRAÇÃO SEGURA
-- ============================================================================
-- Script idempotente - pode ser executado múltiplas vezes sem erros
-- Todas as colunas, FKs e índices são verificados antes de criar
-- ============================================================================

SELECT 'Migração multi-tenant concluída com sucesso!' AS status;
