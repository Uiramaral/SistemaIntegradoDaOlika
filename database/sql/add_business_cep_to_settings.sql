-- ============================================
-- ADICIONAR CEP DA LOJA EM SETTINGS
-- ============================================

-- Adicionar coluna business_cep se não existir
SET @sql = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'settings' 
     AND COLUMN_NAME = 'business_cep') > 0,
    'SELECT "Coluna business_cep já existe - OK!" AS message;',
    'ALTER TABLE `settings` ADD COLUMN `business_cep` VARCHAR(10) NULL AFTER `business_longitude`;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar resultado
SELECT 'Campo business_cep adicionado/verificado!' AS status;
