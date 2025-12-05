-- Criar tabela ai_exceptions para controle de exceções temporárias de IA
-- Executado em: 2025-01-31
-- Descrição: Armazena exceções temporárias que desabilitam IA para números específicos

-- Verificar se a tabela já existe antes de criar
SET @dbname = DATABASE();
SET @tablename = "ai_exceptions";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
  ) > 0,
  "SELECT 'Tabela ai_exceptions já existe' AS resultado;",
  CONCAT("CREATE TABLE `", @tablename, "` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) NOT NULL COMMENT 'Número de telefone (apenas dígitos)',
    `reason` VARCHAR(100) NULL COMMENT 'Motivo da exceção (ex: image_received, video_received, manual_override)',
    `active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status ativo/inativo',
    `expires_at` TIMESTAMP NULL COMMENT 'Data de expiração automática (ex: 5 minutos)',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    INDEX `idx_phone` (`phone`),
    INDEX `idx_active` (`active`),
    INDEX `idx_expires_at` (`expires_at`),
    INDEX `idx_phone_active_expires` (`phone`, `active`, `expires_at`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
));
PREPARE createIfNotExists FROM @preparedStatement;
EXECUTE createIfNotExists;
DEALLOCATE PREPARE createIfNotExists;

-- Versão simples (se preferir executar diretamente):
-- CREATE TABLE `ai_exceptions` (
--   `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
--   `phone` VARCHAR(20) NOT NULL COMMENT 'Número de telefone (apenas dígitos)',
--   `reason` VARCHAR(100) NULL COMMENT 'Motivo da exceção (ex: image_received, video_received, manual_override)',
--   `active` BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Status ativo/inativo',
--   `expires_at` TIMESTAMP NULL COMMENT 'Data de expiração automática (ex: 5 minutos)',
--   `created_at` TIMESTAMP NULL,
--   `updated_at` TIMESTAMP NULL,
--   INDEX `idx_phone` (`phone`),
--   INDEX `idx_active` (`active`),
--   INDEX `idx_expires_at` (`expires_at`),
--   INDEX `idx_phone_active_expires` (`phone`, `active`, `expires_at`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpar exceções expiradas (opcional - pode ser executado periodicamente)
-- DELETE FROM ai_exceptions 
-- WHERE expires_at IS NOT NULL 
-- AND expires_at < NOW() 
-- AND active = TRUE;

