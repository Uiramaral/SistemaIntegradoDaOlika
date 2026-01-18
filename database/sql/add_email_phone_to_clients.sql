-- Adicionar colunas email e phone à tabela clients
-- Execute este SQL no banco de produção

ALTER TABLE `clients` 
ADD COLUMN `email` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Email do estabelecimento' AFTER `name`,
ADD COLUMN `phone` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Telefone do estabelecimento' AFTER `email`;

-- Verificar se as colunas foram adicionadas
DESCRIBE `clients`;
