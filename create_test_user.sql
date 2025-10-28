-- Script SQL para criar usuário de teste
-- Execute este script no seu banco de dados MySQL

INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at) 
VALUES (
    'Admin', 
    'admin@olika.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    NOW(), 
    NOW(), 
    NOW()
);

-- Ou se preferir senha 123456:
-- '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- senha: password
-- '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- senha: 123456

-- Para gerar hash da senha 123456, use:
-- php -r "echo password_hash('123456', PASSWORD_DEFAULT);"
