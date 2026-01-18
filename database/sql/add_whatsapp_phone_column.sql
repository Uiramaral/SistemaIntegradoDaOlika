-- Adicionar coluna whatsapp_phone na tabela whatsapp_settings
-- Executado em: 2025-01-30

-- Verificar se a coluna já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = "whatsapp_settings";
SET @columnname = "whatsapp_phone";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna whatsapp_phone já existe na tabela whatsapp_settings' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(20) NULL AFTER sender_name;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Versão simples (se preferir executar diretamente):
-- ALTER TABLE whatsapp_settings 
-- ADD COLUMN whatsapp_phone VARCHAR(20) NULL AFTER sender_name;

-- Atualizar registros existentes com número padrão (opcional)
-- UPDATE whatsapp_settings 
-- SET whatsapp_phone = '5571987019420' 
-- WHERE whatsapp_phone IS NULL AND active = 1;

