-- Limpar valores inválidos (emails) do campo botconversa_paid_webhook_url
-- Este script limpa valores que contêm @ mas não são URLs válidas
-- Na tabela payment_settings

UPDATE payment_settings 
SET value = '' 
WHERE `key` = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

-- Verificar o resultado:
SELECT `key`, value FROM payment_settings WHERE `key` = 'botconversa_paid_webhook_url';

