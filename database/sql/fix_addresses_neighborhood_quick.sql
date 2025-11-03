-- ============================================
-- QUICK FIX: Corrigir coluna addresses
-- SQL direto - Execute no phpMyAdmin
-- ============================================

-- Se a coluna 'district' existir, renomear para 'neighborhood'
-- (Este comando funciona mesmo se neighborhood já existir - vai dar erro mas não quebra nada)

-- Verificar primeiro:
SHOW COLUMNS FROM `addresses` WHERE Field = 'district';
SHOW COLUMNS FROM `addresses` WHERE Field = 'neighborhood';

-- Se district existe e neighborhood NÃO existe, execute:
ALTER TABLE `addresses` CHANGE `district` `neighborhood` VARCHAR(255) NULL DEFAULT NULL;

-- Se AMBAS existem, execute os 3 comandos abaixo:
-- 1. Copiar dados
UPDATE `addresses` SET `neighborhood` = `district` WHERE `district` IS NOT NULL AND (`neighborhood` IS NULL OR `neighborhood` = '');

-- 2. Remover district
ALTER TABLE `addresses` DROP COLUMN `district`;

-- 3. Verificar resultado
SHOW COLUMNS FROM `addresses`;

