-- ==============================================
-- ADICIONAR client_id NA TABELA users
-- ==============================================

-- Adicionar coluna client_id na tabela users (se não existir)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS client_id BIGINT UNSIGNED DEFAULT NULL AFTER id;

-- Criar índice para performance
CREATE INDEX IF NOT EXISTS idx_users_client_id ON users(client_id);

-- Adicionar foreign key
ALTER TABLE users 
ADD CONSTRAINT IF NOT EXISTS fk_users_clients 
FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE;

-- Vincular usuários existentes ao cliente Olika (ID 1)
UPDATE users SET client_id = 1 WHERE client_id IS NULL;

