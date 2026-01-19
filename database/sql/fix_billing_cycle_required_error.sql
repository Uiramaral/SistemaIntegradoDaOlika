-- ================================================================
-- CORREÇÃO: Erro "The billing cycle field is required"
-- ================================================================
-- A coluna billing_cycle já existe. Este script garante que está
-- configurada corretamente com DEFAULT 'monthly'
-- ================================================================

-- PASSO 1: Garantir que a coluna tem DEFAULT 'monthly'
-- ================================================================
ALTER TABLE `plans` 
MODIFY COLUMN `billing_cycle` ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly' 
COMMENT 'Ciclo de cobrança (sempre mensal por enquanto)';

-- ================================================================
-- PASSO 2: Atualizar todos os planos existentes
-- ================================================================
UPDATE `plans` 
SET `billing_cycle` = 'monthly'
WHERE `billing_cycle` IS NULL OR `billing_cycle` != 'monthly';

-- ================================================================
-- PASSO 3: Verificação final
-- ================================================================
SELECT 
    `id`, 
    `name`, 
    `billing_cycle`, 
    `price`,
    CASE 
        WHEN `billing_cycle` IS NULL THEN 'ERRO: NULL encontrado!'
        WHEN `billing_cycle` != 'monthly' THEN 'AVISO: Não é monthly!'
        ELSE 'OK'
    END AS status
FROM `plans` 
ORDER BY `id`;

-- Deve retornar todos os registros com billing_cycle = 'monthly' e status = 'OK'
