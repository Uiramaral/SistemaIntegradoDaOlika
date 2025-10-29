-- Script para verificar se o problema é de domínio ou rota
-- Execute este SQL para verificar configurações

-- 1. Verificar se o usuário está logado corretamente
SELECT '=== USUÁRIO LOGADO ===' as info;
SELECT id, name, email, 
    CASE 
        WHEN password LIKE '$2y$%' THEN 'bcrypt (correto)'
        WHEN password LIKE '$2a$%' THEN 'bcrypt (versão antiga)'
        WHEN LENGTH(password) = 32 THEN 'MD5 (incorreto)'
        WHEN LENGTH(password) = 40 THEN 'SHA1 (incorreto)'
        ELSE 'Formato desconhecido'
    END as formato_senha
FROM users WHERE email = 'admin@olika.com';

-- 2. Verificar se as tabelas existem
SELECT '=== TABELAS EXISTENTES ===' as info;
SHOW TABLES LIKE '%orders%';
SHOW TABLES LIKE '%customers%';
SHOW TABLES LIKE '%products%';

-- 3. Verificar estrutura da tabela orders
SELECT '=== ESTRUTURA ORDERS ===' as info;
DESCRIBE orders;
