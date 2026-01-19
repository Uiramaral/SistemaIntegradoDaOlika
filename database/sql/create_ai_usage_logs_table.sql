-- ============================================
-- Sistema de Consumo de Créditos de IA para SaaS Multi-tenant
-- Olika Tecnologia - 2026
-- ============================================

-- 1. Adicionar campos de saldo/consumo de IA na tabela clients
-- Adicionar coluna ai_balance se não existir
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'ai_balance'
) = 0,
'ALTER TABLE clients ADD COLUMN ai_balance DECIMAL(10,6) DEFAULT 10.00 COMMENT "Saldo de créditos de IA em BRL"',
'SELECT "Coluna ai_balance já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar coluna ai_tokens_used se não existir
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'ai_tokens_used'
) = 0,
'ALTER TABLE clients ADD COLUMN ai_tokens_used INT DEFAULT 0 COMMENT "Total de tokens consumidos"',
'SELECT "Coluna ai_tokens_used já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar coluna ai_requests_count se não existir
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'ai_requests_count'
) = 0,
'ALTER TABLE clients ADD COLUMN ai_requests_count INT DEFAULT 0 COMMENT "Total de requisições de IA"',
'SELECT "Coluna ai_requests_count já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar coluna ai_last_used_at se não existir
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'ai_last_used_at'
) = 0,
'ALTER TABLE clients ADD COLUMN ai_last_used_at TIMESTAMP NULL COMMENT "Última utilização de IA"',
'SELECT "Coluna ai_last_used_at já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Criar tabela de log de consumo de IA
CREATE TABLE IF NOT EXISTS ai_usage_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL COMMENT 'FK para clients',
    model VARCHAR(50) NOT NULL COMMENT 'Modelo utilizado (gemini-2.5-flash, etc)',
    task_type VARCHAR(30) DEFAULT 'chat' COMMENT 'Tipo de tarefa (chat, marketing, analysis)',
    input_tokens INT NOT NULL DEFAULT 0 COMMENT 'Tokens de entrada',
    output_tokens INT NOT NULL DEFAULT 0 COMMENT 'Tokens de saída',
    cost_usd DECIMAL(12,8) NOT NULL DEFAULT 0 COMMENT 'Custo real em USD',
    cost_brl DECIMAL(12,6) NOT NULL DEFAULT 0 COMMENT 'Custo real em BRL',
    charged_brl DECIMAL(12,6) NOT NULL DEFAULT 0 COMMENT 'Valor cobrado do cliente em BRL (com markup)',
    profit_brl DECIMAL(12,6) NOT NULL DEFAULT 0 COMMENT 'Lucro em BRL',
    prompt_preview VARCHAR(255) NULL COMMENT 'Preview do prompt (primeiros 255 chars)',
    response_preview VARCHAR(255) NULL COMMENT 'Preview da resposta (primeiros 255 chars)',
    success BOOLEAN DEFAULT TRUE COMMENT 'Se a requisição foi bem-sucedida',
    error_message TEXT NULL COMMENT 'Mensagem de erro se falhou',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_client_id (client_id),
    INDEX idx_model (model),
    INDEX idx_task_type (task_type),
    INDEX idx_created_at (created_at),
    INDEX idx_client_created (client_id, created_at),
    
    CONSTRAINT fk_ai_usage_logs_client_id FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de consumo de IA por estabelecimento';

-- 3. Criar view para relatório de lucro por estabelecimento
CREATE OR REPLACE VIEW v_ai_profit_by_client AS
SELECT 
    c.id AS client_id,
    c.name AS client_name,
    c.slug,
    c.ai_balance,
    c.ai_tokens_used,
    c.ai_requests_count,
    COALESCE(SUM(l.input_tokens), 0) AS total_input_tokens,
    COALESCE(SUM(l.output_tokens), 0) AS total_output_tokens,
    COALESCE(SUM(l.input_tokens + l.output_tokens), 0) AS total_tokens,
    COALESCE(SUM(l.cost_usd), 0) AS total_cost_usd,
    COALESCE(SUM(l.cost_brl), 0) AS total_cost_brl,
    COALESCE(SUM(l.charged_brl), 0) AS total_charged_brl,
    COALESCE(SUM(l.profit_brl), 0) AS total_profit_brl,
    COALESCE(COUNT(l.id), 0) AS requests_count,
    COALESCE(SUM(CASE WHEN l.success = 0 THEN 1 ELSE 0 END), 0) AS error_count,
    MAX(l.created_at) AS last_request_at
FROM clients c
LEFT JOIN ai_usage_logs l ON c.id = l.client_id
WHERE c.ai_enabled = 1
GROUP BY c.id, c.name, c.slug, c.ai_balance, c.ai_tokens_used, c.ai_requests_count;

-- 4. View para relatório mensal de lucro
CREATE OR REPLACE VIEW v_ai_monthly_profit AS
SELECT 
    DATE_FORMAT(l.created_at, '%Y-%m') AS month,
    c.id AS client_id,
    c.name AS client_name,
    l.model,
    COUNT(*) AS requests_count,
    SUM(l.input_tokens + l.output_tokens) AS total_tokens,
    SUM(l.cost_usd) AS total_cost_usd,
    SUM(l.cost_brl) AS total_cost_brl,
    SUM(l.charged_brl) AS total_charged_brl,
    SUM(l.profit_brl) AS total_profit_brl,
    ROUND((SUM(l.profit_brl) / NULLIF(SUM(l.cost_brl), 0)) * 100, 2) AS profit_margin_percent
FROM ai_usage_logs l
JOIN clients c ON c.id = l.client_id
WHERE l.success = 1
GROUP BY DATE_FORMAT(l.created_at, '%Y-%m'), c.id, c.name, l.model
ORDER BY month DESC, total_profit_brl DESC;

-- 5. View para totais globais do SaaS
CREATE OR REPLACE VIEW v_ai_saas_totals AS
SELECT 
    COUNT(DISTINCT client_id) AS clients_with_ai,
    COUNT(*) AS total_requests,
    SUM(input_tokens + output_tokens) AS total_tokens,
    SUM(cost_usd) AS total_cost_usd,
    SUM(cost_brl) AS total_cost_brl,
    SUM(charged_brl) AS total_charged_brl,
    SUM(profit_brl) AS total_profit_brl,
    ROUND((SUM(profit_brl) / NULLIF(SUM(cost_brl), 0)) * 100, 2) AS profit_margin_percent,
    SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) AS total_errors
FROM ai_usage_logs
WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01');

-- 6. Inserir configurações de preço no master_settings
INSERT INTO master_settings (`key`, `value`, `type`, `description`) VALUES
('ai_exchange_rate', '5.50', 'decimal', 'Taxa de câmbio USD -> BRL'),
('ai_default_markup', '3.0', 'decimal', 'Markup padrão do SaaS (3x = 200% margem)'),
('ai_min_balance', '0.01', 'decimal', 'Saldo mínimo para usar IA'),
('ai_default_balance', '10.00', 'decimal', 'Saldo inicial para novos clientes')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- 7. Atualizar clientes existentes com saldo inicial
UPDATE clients 
SET ai_balance = 10.00, 
    ai_tokens_used = 0, 
    ai_requests_count = 0 
WHERE ai_balance IS NULL;

SELECT 'Migration concluída com sucesso!' AS status;
