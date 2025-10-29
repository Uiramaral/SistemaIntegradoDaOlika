-- Script para corrigir a senha do usuário admin
-- Senha: 123456
-- Hash gerado: $2y$10$.s5I2uFHeKz8XOyIDW.Ctu2eJNmwAoAEhLzY6M4GOkVuItbMHwDo6

-- Atualizar a senha do usuário admin
UPDATE users SET password = '$2y$10$.s5I2uFHeKz8XOyIDW.Ctu2eJNmwAoAEhLzY6M4GOkVuItbMHwDo6' WHERE email = 'admin@olika.com';

-- Se não existir o usuário admin, criar um
INSERT INTO users (name, email, password, created_at, updated_at) 
VALUES ('Admin', 'admin@olika.com', '$2y$10$.s5I2uFHeKz8XOyIDW.Ctu2eJNmwAoAEhLzY6M4GOkVuItbMHwDo6', NOW(), NOW())
ON DUPLICATE KEY UPDATE password = '$2y$10$.s5I2uFHeKz8XOyIDW.Ctu2eJNmwAoAEhLzY6M4GOkVuItbMHwDo6';

-- Verificar se foi atualizado corretamente
SELECT id, name, email, password FROM users WHERE email = 'admin@olika.com';
