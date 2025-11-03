-- Limpar valores inválidos (emails) do campo botconversa_paid_webhook_url
-- Este script verifica a estrutura da tabela antes de executar

-- Primeiro, verifique a estrutura da sua tabela settings:
-- DESCRIBE settings;

-- Depois, execute o UPDATE apropriado baseado na estrutura encontrada:

-- Se sua tabela settings usa 'name' como chave:
UPDATE settings 
SET value = '' 
WHERE name = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

-- OU se usa 'key' como chave (com backticks para escapar palavra reservada):
UPDATE settings 
SET `value` = '' 
WHERE `key` = 'botconversa_paid_webhook_url'
  AND `value` LIKE '%@%' 
  AND `value` NOT LIKE 'http%://%';

-- OU se usa 'config_key':
UPDATE settings 
SET value = '' 
WHERE config_key = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

-- Para payment_settings (se usar como fallback):
UPDATE payment_settings 
SET value = '' 
WHERE `key` = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

