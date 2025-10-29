-- Script para verificar o formato da senha no banco
-- Execute este SQL para verificar se a senha está no formato correto

-- Verificar se existe o usuário admin
SELECT 
    id,
    name,
    email,
    password,
    CASE 
        WHEN password LIKE '$2y$%' THEN 'bcrypt (correto)'
        WHEN password LIKE '$2a$%' THEN 'bcrypt (versão antiga)'
        WHEN LENGTH(password) = 32 THEN 'MD5 (incorreto)'
        WHEN LENGTH(password) = 40 THEN 'SHA1 (incorreto)'
        WHEN LENGTH(password) = 64 THEN 'SHA256 (incorreto)'
        ELSE 'Formato desconhecido'
    END as formato_senha
FROM users 
WHERE email = 'admin@olika.com';

-- Se não existir, mostrar todos os usuários
SELECT 
    id,
    name,
    email,
    password,
    CASE 
        WHEN password LIKE '$2y$%' THEN 'bcrypt (correto)'
        WHEN password LIKE '$2a$%' THEN 'bcrypt (versão antiga)'
        WHEN LENGTH(password) = 32 THEN 'MD5 (incorreto)'
        WHEN LENGTH(password) = 40 THEN 'SHA1 (incorreto)'
        WHEN LENGTH(password) = 64 THEN 'SHA256 (incorreto)'
        ELSE 'Formato desconhecido'
    END as formato_senha
FROM users;
