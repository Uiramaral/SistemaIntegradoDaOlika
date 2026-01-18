-- SQL Simples para adicionar coluna whatsapp_phone
-- Execute este comando no seu banco de dados MySQL/MariaDB

ALTER TABLE whatsapp_settings 
ADD COLUMN IF NOT EXISTS whatsapp_phone VARCHAR(20) NULL 
AFTER sender_name;

-- Se o IF NOT EXISTS não funcionar (versões antigas do MySQL), use:
-- ALTER TABLE whatsapp_settings 
-- ADD COLUMN whatsapp_phone VARCHAR(20) NULL 
-- AFTER sender_name;

-- Opcional: Atualizar registros existentes com número padrão
UPDATE whatsapp_settings 
SET whatsapp_phone = '5571987019420' 
WHERE whatsapp_phone IS NULL AND active = 1;

