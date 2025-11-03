-- Limpar valores inválidos (emails) do campo botconversa_paid_webhook_url
-- Este script limpa valores que contêm @ mas não são URLs válidas

-- Para tabela settings (ajuste o nome da coluna conforme sua estrutura)
-- Verifique primeiro qual é a estrutura da sua tabela:
-- DESCRIBE settings;

-- Opção 1: Se a coluna se chama 'name'
UPDATE settings 
SET value = '' 
WHERE name = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

-- Opção 2: Se a coluna se chama 'key' (use backticks para escapar palavra reservada)
UPDATE settings 
SET `value` = '' 
WHERE `key` = 'botconversa_paid_webhook_url'
  AND `value` LIKE '%@%' 
  AND `value` NOT LIKE 'http%://%';

-- Opção 3: Se a coluna se chama 'config_key'
UPDATE settings 
SET value = '' 
WHERE config_key = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

-- Para tabela payment_settings (fallback)
UPDATE payment_settings 
SET value = '' 
WHERE `key` = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

