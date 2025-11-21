-- Script para converter INSERTs do backup e importar apenas clientes inexistentes
-- Use este script após importar a tabela clientes do backup

-- Opção 1: Usar INSERT IGNORE (mais simples, ignora duplicatas por telefone)
-- Primeiro, adicione UNIQUE na coluna phone se não tiver:
-- ALTER TABLE `customers` ADD UNIQUE INDEX `customers_phone_unique` (`phone`);

-- Depois execute:
INSERT IGNORE INTO `customers` (`name`, `phone`, `email`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`)
SELECT 
    c.nome as name,
    c.telefone as phone,
    c.email,
    c.email_verified_at,
    c.remember_token,
    COALESCE(c.data_de_cadastro, c.created_at, NOW()) as created_at,
    COALESCE(c.updated_at, NOW()) as updated_at
FROM `clientes` c
WHERE c.telefone IS NOT NULL
AND TRIM(c.telefone) != '';

-- Opção 2: Verificar antes de inserir (mais seguro, não precisa UNIQUE)
INSERT INTO `customers` (`name`, `phone`, `email`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`)
SELECT 
    c.nome as name,
    c.telefone as phone,
    c.email,
    c.email_verified_at,
    c.remember_token,
    COALESCE(c.data_de_cadastro, c.created_at, NOW()) as created_at,
    COALESCE(c.updated_at, NOW()) as updated_at
FROM `clientes` c
LEFT JOIN `customers` cu ON c.telefone = cu.phone
WHERE cu.id IS NULL
AND c.telefone IS NOT NULL
AND TRIM(c.telefone) != '';



