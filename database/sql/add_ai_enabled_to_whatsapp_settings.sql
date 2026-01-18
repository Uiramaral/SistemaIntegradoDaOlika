-- Adicionar coluna ai_enabled na tabela whatsapp_settings
-- Executado em: 2025-01-31
-- Descrição: Adiciona flag para controle global de IA

-- Verificar se a coluna já existe antes de adicionar
SET @dbname = DATABASE();
SET @tablename = "whatsapp_settings";
SET @columnname = "ai_enabled";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna ai_enabled já existe na tabela whatsapp_settings' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " BOOLEAN NOT NULL DEFAULT FALSE AFTER active;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Versão simples (se preferir executar diretamente):
-- ALTER TABLE whatsapp_settings 
-- ADD COLUMN ai_enabled BOOLEAN NOT NULL DEFAULT FALSE AFTER active;

-- Atualizar registros existentes (opcional - manter IA desabilitada por padrão)
-- UPDATE whatsapp_settings 
-- SET ai_enabled = FALSE 
-- WHERE ai_enabled IS NULL;

