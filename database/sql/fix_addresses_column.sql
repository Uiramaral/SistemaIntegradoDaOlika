-- ============================================
-- SQL para garantir que addresses tem 'neighborhood' e não 'district'
-- Execute este SQL diretamente no phpMyAdmin
-- ============================================

-- 1. Se 'district' existe, renomear para 'neighborhood'
-- (Ignora erro se neighborhood já existe)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'addresses' 
     AND COLUMN_NAME = 'district') > 0
    AND
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'addresses' 
     AND COLUMN_NAME = 'neighborhood') = 0,
    'ALTER TABLE `addresses` CHANGE `district` `neighborhood` VARCHAR(255) NULL DEFAULT NULL;',
    'SELECT "Coluna district não existe ou neighborhood já existe" AS message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Se ambas existem, copiar dados e remover district
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'addresses' 
     AND COLUMN_NAME = 'district') > 0
    AND
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'addresses' 
     AND COLUMN_NAME = 'neighborhood') > 0,
    'UPDATE `addresses` SET `neighborhood` = `district` WHERE `district` IS NOT NULL AND (`neighborhood` IS NULL OR `neighborhood` = ''''); ALTER TABLE `addresses` DROP COLUMN `district`;',
    'SELECT "Não é necessário copiar ou remover district" AS message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Se neighborhood não existe, criar
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'addresses' 
     AND COLUMN_NAME = 'neighborhood') = 0,
    'ALTER TABLE `addresses` ADD COLUMN `neighborhood` VARCHAR(255) NULL DEFAULT NULL AFTER `complement`;',
    'SELECT "Coluna neighborhood já existe" AS message;'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar resultado final
SHOW COLUMNS FROM `addresses`;

