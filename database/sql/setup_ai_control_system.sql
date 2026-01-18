-- ============================================
-- CONFIGURAÇÃO COMPLETA DO SISTEMA DE CONTROLE DE IA
-- Executado em: 2025-01-31
-- Descrição: Cria todas as tabelas e colunas necessárias para o controle condicional de IA
-- ============================================

-- ============================================
-- 1. ADICIONAR COLUNA ai_enabled NA TABELA whatsapp_settings
-- ============================================
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

-- ============================================
-- 2. CRIAR TABELA ai_exceptions
-- ============================================
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

-- ============================================
-- 3. LIMPAR EXCEÇÕES EXPIRADAS (OPCIONAL)
-- ============================================
-- Descomente a linha abaixo para limpar exceções expiradas ao executar o script
-- UPDATE ai_exceptions SET active = FALSE WHERE expires_at IS NOT NULL AND expires_at < NOW() AND active = TRUE;

-- ============================================
-- 4. VERIFICAÇÃO FINAL
-- ============================================
SELECT 
    'Configuração do Sistema de Controle de IA' AS titulo,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE table_schema = @dbname 
            AND table_name = 'whatsapp_settings' 
            AND column_name = 'ai_enabled'
        ) THEN '✓ Coluna ai_enabled criada'
        ELSE '✗ Coluna ai_enabled NÃO encontrada'
    END AS status_coluna,
    CASE 
        WHEN EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.TABLES 
            WHERE table_schema = @dbname 
            AND table_name = 'ai_exceptions'
        ) THEN '✓ Tabela ai_exceptions criada'
        ELSE '✗ Tabela ai_exceptions NÃO encontrada'
    END AS status_tabela;

