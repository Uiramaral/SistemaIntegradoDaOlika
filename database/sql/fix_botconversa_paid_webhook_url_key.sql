-- Limpar valores inválidos (emails) do campo botconversa_paid_webhook_url
-- USE ESTE SCRIPT se sua tabela settings usa 'key' como coluna de chave
-- (key é palavra reservada, precisa de backticks)

UPDATE settings 
SET `value` = '' 
WHERE `key` = 'botconversa_paid_webhook_url'
  AND `value` LIKE '%@%' 
  AND `value` NOT LIKE 'http%://%';

