-- SQL Completo: Importar clientes do backup que não existem
-- Passo 1: Criar tabela temporária clientes (se não existir)

CREATE TABLE IF NOT EXISTS `clientes` (
    `id` INT NOT NULL,
    `nome` VARCHAR(255),
    `email` VARCHAR(255),
    `google_id` VARCHAR(255),
    `avatar` VARCHAR(255),
    `email_verified_at` TIMESTAMP NULL,
    `remember_token` VARCHAR(100),
    `telefone` VARCHAR(20),
    `idsubscriber` VARCHAR(255),
    `data_de_cadastro` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Passo 2: Executar os INSERTs do backup aqui
-- (Cole os INSERTs do arquivo SQL entre as linhas abaixo)

-- Passo 3: Inserir apenas clientes que não existem em customers
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

-- Passo 4: Limpar tabela temporária (opcional)
-- DROP TABLE IF EXISTS `clientes`;


