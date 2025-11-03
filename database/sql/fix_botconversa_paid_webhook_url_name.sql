-- Limpar valores inválidos (emails) do campo botconversa_paid_webhook_url
-- USE ESTE SCRIPT se sua tabela settings usa 'name' como coluna de chave

UPDATE settings 
SET value = '' 
WHERE name = 'botconversa_paid_webhook_url'
  AND value LIKE '%@%' 
  AND value NOT LIKE 'http%://%';

