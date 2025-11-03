-- ============================================
-- SQL para corrigir coluna addresses: district -> neighborhood
-- Execute este SQL diretamente no phpMyAdmin ou cliente MySQL
-- ============================================

-- PASSO 1: Verificar estado atual (Execute e veja os resultados)
SHOW COLUMNS FROM `addresses` LIKE 'district';
SHOW COLUMNS FROM `addresses` LIKE 'neighborhood';

-- PASSO 2: Escolha uma das opções abaixo baseado no resultado do PASSO 1

-- ============================================
-- OPÇÃO A: Se 'district' existe e 'neighborhood' NÃO existe
-- ============================================
-- Execute este SQL:
ALTER TABLE `addresses` 
CHANGE COLUMN `district` `neighborhood` VARCHAR(255) NULL DEFAULT NULL;

-- ============================================
-- OPÇÃO B: Se ambas as colunas existem (district E neighborhood)
-- ============================================
-- 1. Primeiro, copiar dados de district para neighborhood (se neighborhood estiver NULL)
UPDATE `addresses` 
SET `neighborhood` = `district` 
WHERE `district` IS NOT NULL AND `neighborhood` IS NULL;

-- 2. Depois, remover a coluna district
ALTER TABLE `addresses` DROP COLUMN `district`;

-- ============================================
-- OPÇÃO C: Se apenas 'neighborhood' já existe (ESTADO CORRETO)
-- ============================================
-- Não execute nada! O banco já está correto.

-- ============================================
-- OPÇÃO D: Se NENHUMA das colunas existe (tabela nova/incompleta)
-- ============================================
ALTER TABLE `addresses` 
ADD COLUMN `neighborhood` VARCHAR(255) NULL DEFAULT NULL 
AFTER `complement`;

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================
-- Execute para confirmar que está correto:
SHOW COLUMNS FROM `addresses`;

-- A tabela deve ter a coluna 'neighborhood' e NÃO deve ter 'district'
