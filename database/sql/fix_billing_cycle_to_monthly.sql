-- ================================================================
-- CORRIGIR BILLING_CYCLE PARA MENSAL
-- ================================================================
-- Por enquanto, não há pagamento anual - todos os planos são mensais

-- Atualizar todos os planos para billing_cycle = 'monthly'
UPDATE `plans` 
SET `billing_cycle` = 'monthly'
WHERE `billing_cycle` != 'monthly' OR `billing_cycle` IS NULL;

-- ================================================================
-- VERIFICAÇÃO
-- ================================================================
-- Confirmar que todos os planos são mensais:
SELECT `id`, `name`, `billing_cycle`, `price` 
FROM `plans` 
ORDER BY `sort_order`;

-- Resultado esperado: billing_cycle = 'monthly' em todos os registros
