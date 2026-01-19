-- ================================================================
-- CONSOLIDAR CONFIGURAÇÃO DE TRIAL DAYS
-- ================================================================
-- Remove duplicidade: Usar APENAS 'registration_trial_days'
-- Remover 'default_trial_days' (redundante)

-- 1. Copiar valor de 'default_trial_days' para 'registration_trial_days' (se necessário)
UPDATE `master_settings` 
SET `value` = (SELECT `value` FROM (SELECT * FROM `master_settings`) AS tmp WHERE `key` = 'default_trial_days')
WHERE `key` = 'registration_trial_days' 
  AND (SELECT `value` FROM (SELECT * FROM `master_settings`) AS tmp WHERE `key` = 'default_trial_days') IS NOT NULL;

-- 2. Excluir 'default_trial_days' (fonte duplicada)
DELETE FROM `master_settings` WHERE `key` = 'default_trial_days';

-- 3. Atualizar descrição para deixar claro que é a única fonte
UPDATE `master_settings` 
SET `description` = 'Dias de período de teste para TODOS os novos cadastros (fonte única)'
WHERE `key` = 'registration_trial_days';

-- ================================================================
-- VERIFICAÇÃO
-- ================================================================
-- Confirmar que apenas 'registration_trial_days' existe:
-- SELECT * FROM `master_settings` WHERE `key` LIKE '%trial%';
