-- ================================================================
-- ADICIONAR COLUNAS DE LIMITES NA TABELA PLANS
-- ================================================================
-- Script idempotente: verifica se cada coluna existe antes de adicionar

-- 1. max_products
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'plans' 
      AND COLUMN_NAME = 'max_products'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `plans` ADD COLUMN `max_products` INT UNSIGNED NULL AFTER `limits`',
    'SELECT "Coluna max_products já existe" AS msg'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. max_orders_per_month
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'plans' 
      AND COLUMN_NAME = 'max_orders_per_month'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `plans` ADD COLUMN `max_orders_per_month` INT UNSIGNED NULL AFTER `max_products`',
    'SELECT "Coluna max_orders_per_month já existe" AS msg'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. max_users
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'plans' 
      AND COLUMN_NAME = 'max_users'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `plans` ADD COLUMN `max_users` INT UNSIGNED NULL AFTER `max_orders_per_month`',
    'SELECT "Coluna max_users já existe" AS msg'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. trial_days (PODE JÁ EXISTIR)
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'plans' 
      AND COLUMN_NAME = 'trial_days'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `plans` ADD COLUMN `trial_days` INT UNSIGNED DEFAULT 14 AFTER `max_users`',
    'SELECT "Coluna trial_days já existe" AS msg'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. billing_cycle
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'plans' 
      AND COLUMN_NAME = 'billing_cycle'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `plans` ADD COLUMN `billing_cycle` ENUM(\'monthly\', \'yearly\') DEFAULT \'monthly\' AFTER `trial_days`',
    'SELECT "Coluna billing_cycle já existe" AS msg'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- VERIFICAÇÃO
-- ================================================================
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'plans'
  AND COLUMN_NAME IN ('max_products', 'max_orders_per_month', 'max_users', 'trial_days', 'billing_cycle')
ORDER BY ORDINAL_POSITION;
