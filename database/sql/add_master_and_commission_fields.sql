-- ================================================================
-- Adicionar campos para controle Master e comissão Mercado Pago
-- Data: 2026-01-18
-- ================================================================

-- 1. Adicionar campo is_master à tabela clients (se não existir)
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'is_master'
) = 0,
'ALTER TABLE clients ADD COLUMN is_master BOOLEAN DEFAULT FALSE COMMENT "Indica se é o estabelecimento master do SaaS" AFTER active',
'SELECT "Coluna is_master já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Adicionar campo mercadopago_commission_enabled (habilitar comissão por venda)
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'mercadopago_commission_enabled'
) = 0,
'ALTER TABLE clients ADD COLUMN mercadopago_commission_enabled BOOLEAN DEFAULT FALSE COMMENT "Habilitar comissão do SaaS por venda via Mercado Pago" AFTER is_master',
'SELECT "Coluna mercadopago_commission_enabled já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Adicionar campo mercadopago_commission_amount (valor fixo da comissão)
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'mercadopago_commission_amount'
) = 0,
'ALTER TABLE clients ADD COLUMN mercadopago_commission_amount DECIMAL(10,2) DEFAULT 0.49 COMMENT "Valor fixo da comissão por venda (padrão R$ 0,49)" AFTER mercadopago_commission_enabled',
'SELECT "Coluna mercadopago_commission_amount já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Adicionar campos de email e phone se não existirem
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'email'
) = 0,
'ALTER TABLE clients ADD COLUMN email VARCHAR(255) NULL COMMENT "E-mail de contato do estabelecimento" AFTER whatsapp_phone',
'SELECT "Coluna email já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'phone'
) = 0,
'ALTER TABLE clients ADD COLUMN phone VARCHAR(20) NULL COMMENT "Telefone de contato do estabelecimento" AFTER email',
'SELECT "Coluna phone já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- Configurações do Mercado Pago Application Fee
-- ================================================================

-- Adicionar configuração na master_settings para taxa de comissão padrão
INSERT INTO master_settings (`key`, `value`, `type`, `description`) VALUES
('mercadopago_default_commission', '0.49', 'decimal', 'Comissão padrão por venda via Mercado Pago (R$)'),
('mercadopago_commission_description', 'Taxa SaaS MenuOlika', 'string', 'Descrição da comissão exibida no Mercado Pago')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ================================================================
-- Índices para melhorar performance
-- ================================================================

-- Índice para buscar clientes master rapidamente
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND INDEX_NAME = 'idx_clients_is_master'
) = 0,
'CREATE INDEX idx_clients_is_master ON clients(is_master)',
'SELECT "Índice idx_clients_is_master já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Índice para buscar clientes com comissão habilitada
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND INDEX_NAME = 'idx_clients_mp_commission'
) = 0,
'CREATE INDEX idx_clients_mp_commission ON clients(mercadopago_commission_enabled, active)',
'SELECT "Índice idx_clients_mp_commission já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- Marcar MenuOlika como master (ajuste o slug conforme necessário)
-- ================================================================

-- Marcar o primeiro cliente ou cliente específico como master
-- AJUSTE O WHERE CLAUSE PARA O SLUG CORRETO DO SEU ESTABELECIMENTO MASTER
UPDATE clients 
SET is_master = TRUE, 
    mercadopago_commission_enabled = FALSE
WHERE slug = 'menuolika' OR id = 1
LIMIT 1;

SELECT '✅ Migração concluída com sucesso!' as status;
SELECT CONCAT('✅ Cliente master: ', name) as master_client FROM clients WHERE is_master = TRUE LIMIT 1;
