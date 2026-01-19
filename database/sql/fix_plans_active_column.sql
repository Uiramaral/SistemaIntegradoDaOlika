-- ================================================================
-- ADICIONAR COLUNA 'active' NA TABELA 'plans'
-- ================================================================
-- Esta migration corrige o erro: "Unknown column 'active' in 'field list'"
-- 
-- Opção 1: Se a coluna 'is_active' já existe, renomeie para 'active'
-- Opção 2: Se não existe nenhuma das duas, crie 'active'

-- VERIFICAR SE 'is_active' EXISTE:
-- Se existir, execute este comando para renomear:
ALTER TABLE `plans` CHANGE COLUMN `is_active` `active` TINYINT(1) NOT NULL DEFAULT 1;

-- SE 'is_active' NÃO EXISTIR, execute este comando para criar 'active':
-- ALTER TABLE `plans` ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `features`;

-- ================================================================
-- VERIFICAÇÃO APÓS EXECUÇÃO
-- ================================================================
-- Execute este comando para confirmar que a coluna foi criada:
-- SHOW COLUMNS FROM `plans` LIKE 'active';

-- ================================================================
-- ATUALIZAR PLANOS EXISTENTES PARA ATIVOS
-- ================================================================
-- Se desejar ativar todos os planos cadastrados:
UPDATE `plans` SET `active` = 1;
