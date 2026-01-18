-- Adicionar campos de filtros e agendamento na tabela whatsapp_campaigns
-- Execute este comando no seu banco de dados MySQL/MariaDB

-- Verificar e adicionar coluna filter_newsletter
SET @dbname = DATABASE();
SET @tablename = "whatsapp_campaigns";
SET @columnname = "filter_newsletter";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna filter_newsletter já existe' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 0 AFTER target_audience;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna filter_customer_type
SET @columnname = "filter_customer_type";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna filter_customer_type já existe' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('all', 'new_customers', 'existing_customers') NOT NULL DEFAULT 'all' AFTER filter_newsletter;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna test_customer_id
SET @columnname = "test_customer_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna test_customer_id já existe' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " BIGINT(20) UNSIGNED NULL AFTER filter_customer_type, ADD CONSTRAINT fk_campaign_test_customer FOREIGN KEY (", @columnname, ") REFERENCES customers(id) ON DELETE SET NULL;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna scheduled_at
SET @columnname = "scheduled_at";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna scheduled_at já existe' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL AFTER test_customer_id;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna scheduled_time
SET @columnname = "scheduled_time";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna scheduled_time já existe' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIME NULL AFTER scheduled_at;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Versão simples (se preferir executar diretamente):
-- ALTER TABLE whatsapp_campaigns 
-- ADD COLUMN filter_newsletter TINYINT(1) NOT NULL DEFAULT 0 AFTER target_audience,
-- ADD COLUMN filter_customer_type ENUM('all', 'new_customers', 'existing_customers') NOT NULL DEFAULT 'all' AFTER filter_newsletter,
-- ADD COLUMN test_customer_id BIGINT(20) UNSIGNED NULL AFTER filter_customer_type,
-- ADD COLUMN scheduled_at TIMESTAMP NULL AFTER test_customer_id,
-- ADD COLUMN scheduled_time TIME NULL AFTER scheduled_at,
-- ADD CONSTRAINT fk_campaign_test_customer FOREIGN KEY (test_customer_id) REFERENCES customers(id) ON DELETE SET NULL;

