-- ==============================================
-- CRIAÇÃO DA TABELA api_tokens (se não existir)
-- ==============================================

CREATE TABLE IF NOT EXISTS api_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL COMMENT 'ID do cliente que possui este token',
    token VARCHAR(80) UNIQUE NOT NULL COMMENT 'Token de autenticação único',
    expires_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Data de expiração (NULL = sem expiração)',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_api_tokens_client_id (client_id),
    INDEX idx_api_tokens_token (token),
    INDEX idx_api_tokens_expires_at (expires_at),
    CONSTRAINT fk_api_tokens_clients FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar índice único para garantir que não haverá tokens duplicados
CREATE UNIQUE INDEX IF NOT EXISTS idx_api_tokens_token_unique ON api_tokens(token);

