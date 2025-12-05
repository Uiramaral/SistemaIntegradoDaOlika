-- Adicionar coluna admin_notification_phone na tabela whatsapp_settings
-- Esta coluna armazena o número do WhatsApp que receberá notificações de admin
-- Execute este comando no seu banco de dados MySQL/MariaDB

-- Verificar se a coluna já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = "whatsapp_settings";
SET @columnname = "admin_notification_phone";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna admin_notification_phone já existe na tabela whatsapp_settings' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) NULL AFTER whatsapp_phone;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Versão simples (se preferir executar diretamente):
-- ALTER TABLE whatsapp_settings 
-- ADD COLUMN admin_notification_phone VARCHAR(20) NULL 
-- AFTER whatsapp_phone;

-- Nota: O número deve estar no formato internacional sem espaços ou caracteres especiais
-- Exemplo: 5571999999999 (55 = código do país Brasil, 71 = DDD, 999999999 = número)

