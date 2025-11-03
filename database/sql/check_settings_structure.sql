-- Verificar a estrutura da tabela settings para saber qual script usar

DESCRIBE settings;

-- Ou verifique diretamente qual é a coluna de chave:
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'settings' 
  AND COLUMN_NAME IN ('name', 'key', 'config_key', 'setting_key', 'option', 'option_name');

-- Verifique também o valor atual (ajuste a coluna conforme encontrado acima):
-- Se usar 'name':
SELECT name, value FROM settings WHERE name = 'botconversa_paid_webhook_url';

-- Se usar 'key':
SELECT `key`, value FROM settings WHERE `key` = 'botconversa_paid_webhook_url';

-- Se usar 'config_key':
SELECT config_key, value FROM settings WHERE config_key = 'botconversa_paid_webhook_url';

