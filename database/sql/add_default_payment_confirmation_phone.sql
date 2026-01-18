-- Adicionar coluna default_payment_confirmation_phone na tabela whatsapp_settings
-- Este campo armazena o número padrão do WhatsApp para envio de confirmações de pagamento
-- Execute este comando no seu banco de dados MySQL/MariaDB

-- Verificar se a coluna já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = "whatsapp_settings";
SET @columnname = "default_payment_confirmation_phone";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna default_payment_confirmation_phone já existe na tabela whatsapp_settings' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) NULL AFTER whatsapp_phone;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Versão simples (se preferir executar diretamente):
-- ALTER TABLE whatsapp_settings 
-- ADD COLUMN default_payment_confirmation_phone VARCHAR(20) NULL AFTER whatsapp_phone;

-- Opcional: Atualizar registros existentes com o número padrão do WhatsApp
-- UPDATE whatsapp_settings 
-- SET default_payment_confirmation_phone = whatsapp_phone 
-- WHERE default_payment_confirmation_phone IS NULL AND whatsapp_phone IS NOT NULL AND active = 1;
