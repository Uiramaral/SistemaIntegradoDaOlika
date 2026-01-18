-- =====================================================================
-- FIX ERROS DO LARAVEL.LOG - 2026-01-11
-- =====================================================================
-- Adiciona campo trial_days na tabela plans
-- =====================================================================
-- IMPORTANTE: Se a coluna já existir, você verá um erro que pode ignorar
--             ou comente a linha se já foi executada
-- =====================================================================

-- Adiciona coluna trial_days na tabela plans
ALTER TABLE `plans` 
ADD COLUMN `trial_days` INT NOT NULL DEFAULT 0 AFTER `sort_order`;

-- =====================================================================
-- FIM
-- =====================================================================
-- Execute este SQL apenas UMA VEZ
-- Se a coluna já existir, você verá: "Duplicate column name 'trial_days'"
-- Isso significa que o campo já foi adicionado anteriormente
-- =====================================================================
