-- ==============================================
-- ATUALIZAR DADOS EXISTENTES COM client_id
-- ==============================================

-- Garantir que o cliente Olika existe (ID 1)
INSERT INTO clients (id, name, slug, plan, instance_url, whatsapp_phone, active, created_at, updated_at)
VALUES (1, 'Olika Cozinha Artesanal', 'olika', 'ia', 'https://pedido.menuonline.com.br', '5571999999999', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE name = name;

-- Atualizar registros existentes para o cliente Olika (ID 1)
UPDATE orders SET client_id = 1 WHERE client_id IS NULL;
UPDATE customers SET client_id = 1 WHERE client_id IS NULL;
UPDATE products SET client_id = 1 WHERE client_id IS NULL;
UPDATE users SET client_id = 1 WHERE client_id IS NULL;

-- Garantir que a tabela api_tokens existe (criar se não existir)
CREATE TABLE IF NOT EXISTS api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(80) UNIQUE NOT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_tokens_client_id (client_id),
    INDEX idx_api_tokens_token (token),
    CONSTRAINT fk_api_tokens_clients FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Garantir que há um token de API para o cliente Olika
INSERT INTO api_tokens (client_id, token, created_at)
SELECT 1, CONCAT('olika_', SUBSTRING(MD5(RAND()), 1, 32), '_', UNIX_TIMESTAMP()), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM api_tokens WHERE client_id = 1
);

-- Vincular instância principal (se não existir)
INSERT INTO instances (url, status, assigned_to, created_at, updated_at)
VALUES ('https://olika.menuonline.com.br', 'assigned', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE assigned_to = 1;

