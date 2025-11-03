-- ============================================
-- SQL SIMPLES - Execute apenas o que você precisa
-- ============================================

-- PRIMEIRO: Verifique qual situação você tem
SELECT 
    CASE WHEN COUNT(*) > 0 THEN 'EXISTE' ELSE 'NÃO EXISTE' END AS district_status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'addresses' 
AND COLUMN_NAME = 'district';

SELECT 
    CASE WHEN COUNT(*) > 0 THEN 'EXISTE' ELSE 'NÃO EXISTE' END AS neighborhood_status
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'addresses' 
AND COLUMN_NAME = 'neighborhood';

-- ============================================
-- SITUAÇÃO 1: district existe, neighborhood NÃO existe
-- Execute este comando:
-- ============================================
ALTER TABLE `addresses` 
CHANGE COLUMN `district` `neighborhood` VARCHAR(255) NULL DEFAULT NULL;

-- ============================================
-- SITUAÇÃO 2: AMBAS existem
-- Execute estes 2 comandos na ordem:
-- ============================================
-- 1. Copiar dados
UPDATE `addresses` 
SET `neighborhood` = `district` 
WHERE `district` IS NOT NULL 
AND (`neighborhood` IS NULL OR `neighborhood` = '');

-- 2. Remover district
ALTER TABLE `addresses` DROP COLUMN `district`;

-- ============================================
-- SITUAÇÃO 3: neighborhood já existe e district NÃO existe
-- ============================================
-- NÃO PRECISA FAZER NADA! Está correto.

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================
SHOW COLUMNS FROM `addresses` WHERE Field IN ('district', 'neighborhood');

