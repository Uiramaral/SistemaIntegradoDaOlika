-- Verificar se a chave está salva em alguma tabela
-- Execute este SQL para verificar onde está armazenada

-- 1. Verificar payment_settings
SELECT 'payment_settings' as source, `key`, `value` 
FROM payment_settings 
WHERE `key` = 'google_maps_api_key' OR `key` LIKE '%google%' OR `key` LIKE '%maps%';

-- 2. Verificar settings (se tiver coluna name - mais comum)
SELECT 'settings (name)' as source, name, value 
FROM settings 
WHERE name = 'google_maps_api_key' OR name LIKE '%google%' OR name LIKE '%maps%';

