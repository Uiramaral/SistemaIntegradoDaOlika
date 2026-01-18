-- =====================================================================
-- FIX ERROS WHATSAPP - 2026-01-11
-- =====================================================================
-- 1. Adiciona timestamps na tabela whatsapp_settings
-- 2. Corrige has_whatsapp do plano "WhatsApp + I.A." (ia)
-- =====================================================================

-- Problema 1: Adicionar colunas created_at e updated_at se não existirem
ALTER TABLE `whatsapp_settings` ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `whatsapp_settings` ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL;

-- Atualizar registros existentes com timestamps
UPDATE `whatsapp_settings` SET `created_at` = NOW(), `updated_at` = NOW() WHERE `created_at` IS NULL;

-- =====================================================================
-- Problema 2: Plano "WhatsApp + I.A." (slug=ia) não tem has_whatsapp=1
-- =====================================================================

-- Verificar planos atuais
SELECT 
    id,
    name,
    slug,
    has_whatsapp,
    has_ai,
    CASE 
        WHEN slug = 'ia' AND has_whatsapp = 0 THEN '✗ PRECISA CORRIGIR'
        WHEN slug = 'ia' AND has_whatsapp = 1 THEN '✓ OK'
        ELSE '-'
    END as status
FROM plans
WHERE slug IN ('basico', 'whatsapp', 'whatsapp-ia', 'ia')
ORDER BY sort_order;

-- Corrigir plano "ia" (WhatsApp + I.A.)
UPDATE `plans` 
SET 
    `has_whatsapp` = 1,
    `has_ai` = 1,
    `slug` = 'whatsapp-ia'
WHERE `slug` = 'ia' OR `name` LIKE '%WhatsApp%I.A%';

-- Garantir que plano "whatsapp" tem has_whatsapp=1
UPDATE `plans` 
SET 
    `has_whatsapp` = 1
WHERE `slug` = 'whatsapp' OR (`name` LIKE '%WhatsApp%' AND `name` NOT LIKE '%I.A%');

-- =====================================================================
-- Verificação final
-- =====================================================================
SELECT 
    id,
    name,
    slug,
    has_whatsapp,
    has_ai,
    CASE 
        WHEN slug IN ('whatsapp', 'whatsapp-ia') AND has_whatsapp = 1 THEN '✓ OK'
        WHEN slug = 'whatsapp-ia' AND has_ai = 1 THEN '✓ OK'
        ELSE '⚠ Verificar'
    END as status
FROM plans
WHERE slug IN ('basico', 'whatsapp', 'whatsapp-ia', 'ia')
ORDER BY sort_order;
