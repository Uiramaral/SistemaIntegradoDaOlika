-- ============================================================================
-- ROLLBACK MULTI-TENANT - DESFAZ TUDO DA VERSÃO 1
-- ============================================================================
-- Execute este script para remover todas as alterações feitas anteriormente
-- Depois execute o script multi_tenant_completo.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- PROCEDURE PARA REMOVER FK DE FORMA SEGURA
-- ============================================================================

DROP PROCEDURE IF EXISTS drop_fk_if_exists;
DELIMITER //
CREATE PROCEDURE drop_fk_if_exists(IN tbl VARCHAR(255), IN fk_name VARCHAR(255))
BEGIN
    DECLARE fk_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO fk_exists FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND CONSTRAINT_NAME = fk_name AND CONSTRAINT_TYPE = 'FOREIGN KEY';
    IF fk_exists > 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` DROP FOREIGN KEY `', fk_name, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- PROCEDURE PARA REMOVER INDICE DE FORMA SEGURA
-- ============================================================================

DROP PROCEDURE IF EXISTS drop_index_if_exists;
DELIMITER //
CREATE PROCEDURE drop_index_if_exists(IN tbl VARCHAR(255), IN idx_name VARCHAR(255))
BEGIN
    DECLARE idx_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO idx_exists FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx_name;
    IF idx_exists > 0 THEN
        SET @sql = CONCAT('DROP INDEX `', idx_name, '` ON `', tbl, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- PROCEDURE PARA REMOVER COLUNA DE FORMA SEGURA
-- ============================================================================

DROP PROCEDURE IF EXISTS drop_column_if_exists;
DELIMITER //
CREATE PROCEDURE drop_column_if_exists(IN tbl VARCHAR(255), IN col_name VARCHAR(255))
BEGIN
    DECLARE col_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col_name;
    IF col_exists > 0 THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` DROP COLUMN `', col_name, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- ============================================================================
-- 1. REMOVER FOREIGN KEYS
-- ============================================================================

CALL drop_fk_if_exists('customers', 'fk_customers_client');
CALL drop_fk_if_exists('orders', 'fk_orders_client');
CALL drop_fk_if_exists('products', 'fk_products_client');
CALL drop_fk_if_exists('deployment_logs', 'fk_deployment_logs_client');
CALL drop_fk_if_exists('categories', 'fk_categories_client');
CALL drop_fk_if_exists('settings', 'fk_settings_client');
CALL drop_fk_if_exists('coupons', 'fk_coupons_client');
CALL drop_fk_if_exists('allergens', 'fk_allergens_client');
CALL drop_fk_if_exists('ingredients', 'fk_ingredients_client');
CALL drop_fk_if_exists('delivery_fees', 'fk_delivery_fees_client');
CALL drop_fk_if_exists('delivery_distance_pricing', 'fk_delivery_distance_pricing_client');
CALL drop_fk_if_exists('delivery_rules', 'fk_delivery_rules_client');
CALL drop_fk_if_exists('delivery_schedules', 'fk_delivery_schedules_client');
CALL drop_fk_if_exists('loyalty_programs', 'fk_loyalty_programs_client');
CALL drop_fk_if_exists('payment_settings', 'fk_payment_settings_client');
CALL drop_fk_if_exists('customer_tags', 'fk_customer_tags_client');
CALL drop_fk_if_exists('whatsapp_instances', 'fk_whatsapp_instances_client');
CALL drop_fk_if_exists('whatsapp_settings', 'fk_whatsapp_settings_client');
CALL drop_fk_if_exists('whatsapp_templates', 'fk_whatsapp_templates_client');
CALL drop_fk_if_exists('whatsapp_campaigns', 'fk_whatsapp_campaigns_client');
CALL drop_fk_if_exists('order_statuses', 'fk_order_statuses_client');
CALL drop_fk_if_exists('analytics_events', 'fk_analytics_events_client');
CALL drop_fk_if_exists('ai_exceptions', 'fk_ai_exceptions_client');
CALL drop_fk_if_exists('users', 'fk_users_client');

-- ============================================================================
-- 2. REMOVER ÍNDICES
-- ============================================================================

CALL drop_index_if_exists('categories', 'idx_categories_client');
CALL drop_index_if_exists('products', 'idx_products_client_active');
CALL drop_index_if_exists('products', 'idx_products_client_catalog');
CALL drop_index_if_exists('customers', 'idx_customers_client_active');
CALL drop_index_if_exists('customers', 'idx_customers_client_phone');
CALL drop_index_if_exists('orders', 'idx_orders_client_status');
CALL drop_index_if_exists('orders', 'idx_orders_client_payment');
CALL drop_index_if_exists('orders', 'idx_orders_client_date');
CALL drop_index_if_exists('coupons', 'idx_coupons_client_active');
CALL drop_index_if_exists('settings', 'idx_settings_client_unique');
CALL drop_index_if_exists('delivery_fees', 'idx_delivery_fees_client');
CALL drop_index_if_exists('delivery_distance_pricing', 'idx_delivery_distance_pricing_client');
CALL drop_index_if_exists('delivery_rules', 'idx_delivery_rules_client');
CALL drop_index_if_exists('delivery_schedules', 'idx_delivery_schedules_client');
CALL drop_index_if_exists('whatsapp_instances', 'idx_whatsapp_instances_client');
CALL drop_index_if_exists('whatsapp_campaigns', 'idx_whatsapp_campaigns_client');
CALL drop_index_if_exists('loyalty_programs', 'idx_loyalty_programs_client');
CALL drop_index_if_exists('customer_tags', 'idx_customer_tags_client');
CALL drop_index_if_exists('order_statuses', 'idx_order_statuses_client');
CALL drop_index_if_exists('analytics_events', 'idx_analytics_client_date');
CALL drop_index_if_exists('users', 'idx_users_client');
CALL drop_index_if_exists('users', 'idx_users_email');

-- ============================================================================
-- 3. REMOVER TRIGGER
-- ============================================================================

DROP TRIGGER IF EXISTS `trg_settings_per_client`;
DROP TRIGGER IF EXISTS `trg_settings_singleton`;

-- ============================================================================
-- 4. REMOVER COLUNAS (apenas das tabelas que adicionamos)
-- ============================================================================

-- Nota: NÃO removemos client_id de customers, orders, products, deployment_logs
-- pois essas tabelas JÁ TINHAM a coluna antes da migração

CALL drop_column_if_exists('categories', 'client_id');
CALL drop_column_if_exists('settings', 'client_id');
CALL drop_column_if_exists('coupons', 'client_id');
CALL drop_column_if_exists('allergens', 'client_id');
CALL drop_column_if_exists('ingredients', 'client_id');
CALL drop_column_if_exists('delivery_fees', 'client_id');
CALL drop_column_if_exists('delivery_distance_pricing', 'client_id');
CALL drop_column_if_exists('delivery_rules', 'client_id');
CALL drop_column_if_exists('delivery_schedules', 'client_id');
CALL drop_column_if_exists('loyalty_programs', 'client_id');
CALL drop_column_if_exists('payment_settings', 'client_id');
CALL drop_column_if_exists('customer_tags', 'client_id');
CALL drop_column_if_exists('whatsapp_instances', 'client_id');
CALL drop_column_if_exists('whatsapp_settings', 'client_id');
CALL drop_column_if_exists('whatsapp_templates', 'client_id');
CALL drop_column_if_exists('whatsapp_campaigns', 'client_id');
CALL drop_column_if_exists('order_statuses', 'client_id');
CALL drop_column_if_exists('analytics_events', 'client_id');
CALL drop_column_if_exists('ai_exceptions', 'client_id');
CALL drop_column_if_exists('users', 'client_id');
CALL drop_column_if_exists('users', 'role');

-- ============================================================================
-- LIMPAR PROCEDURES
-- ============================================================================

DROP PROCEDURE IF EXISTS drop_fk_if_exists;
DROP PROCEDURE IF EXISTS drop_index_if_exists;
DROP PROCEDURE IF EXISTS drop_column_if_exists;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Rollback concluído! Agora execute multi_tenant_completo.sql' AS status;
