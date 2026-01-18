-- ============================================================================
-- CORREÇÕES SISTEMA OLIKA - EXECUÇÃO ÚNICA
-- Data: 2025-12-18
-- Descrição: Adiciona campos WhatsApp e corrige valores de pedidos agendados
-- ============================================================================

SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

-- 1. ADICIONAR CAMPOS À TABELA whatsapp_instance_urls
-- ----------------------------------------------------------------------------
-- Verificar e adicionar coluna api_key
SET @dbname = DATABASE();
SET @tablename = 'whatsapp_instance_urls';
SET @columnname = 'api_key';
SET @preparedStatement = (
    SELECT IF(
        COUNT(*) = 0,
        CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL COMMENT "API Key da instância WhatsApp" AFTER url;'),
        'SELECT "Coluna api_key já existe" as msg;'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
);
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna description
SET @columnname = 'description';
SET @preparedStatement = (
    SELECT IF(
        COUNT(*) = 0,
        CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TEXT NULL COMMENT "Descrição adicional da instância" AFTER api_key;'),
        'SELECT "Coluna description já existe" as msg;'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
);
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 2. CORRIGIR VALORES ZERADOS EM PEDIDOS AGENDADOS
-- ----------------------------------------------------------------------------
UPDATE orders 
SET final_amount = COALESCE(final_amount, total_amount, 0)
WHERE scheduled_delivery_at IS NOT NULL 
  AND (final_amount IS NULL OR final_amount = 0)
  AND total_amount > 0;

-- 3. VERIFICAR RESULTADOS (OPCIONAL - COMENTADO)
-- ----------------------------------------------------------------------------
-- SELECT 
--   COUNT(*) as total_pedidos_agendados,
--   SUM(CASE WHEN final_amount = 0 THEN 1 ELSE 0 END) as pedidos_zerados
-- FROM orders 
-- WHERE scheduled_delivery_at IS NOT NULL;

-- Restaurar configurações
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

-- ============================================================================
-- FIM DA EXECUÇÃO
-- ============================================================================
