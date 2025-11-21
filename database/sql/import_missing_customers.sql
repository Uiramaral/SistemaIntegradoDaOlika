-- SQL para importar apenas clientes que não existem na tabela customers
-- Este script lê da tabela clientes (backup) e insere em customers (sistema atual)
-- Verifica por telefone para evitar duplicatas

-- IMPORTANTE: Execute primeiro o backup para criar a tabela clientes temporária
-- ou ajuste o script para ler diretamente do arquivo SQL

-- Inserir apenas clientes que não existem (verificando por telefone)
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
AND c.telefone != '';



