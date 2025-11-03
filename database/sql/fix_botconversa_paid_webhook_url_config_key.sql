-- Limpar valores inválidos (emails) do campo botconversa_paid_webhook_url
-- USE ESTE SCRIPT se sua tabela settings usa 'config_key' como coluna de chave

UPDATE settings 
SET value = '' 
WHERE config_key = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

