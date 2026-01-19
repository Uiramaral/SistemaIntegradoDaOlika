-- ================================================================
-- ADICIONAR COLUNAS NA TABELA PLANS (Versão Segura)
-- ================================================================
-- Use este script se o servidor não permite PREPARE statements
-- Execute cada ALTER TABLE individualmente e ignore erros de "coluna já existe"

-- INSTRUÇÕES:
-- 1. Execute cada comando separadamente no phpMyAdmin
-- 2. Se aparecer erro "Duplicate column name", ignore e continue
-- 3. Se aparecer "access denied", selecione o banco correto no menu lateral

-- ================================================================
-- PASSO 1: Adicionar max_products
-- ================================================================
ALTER TABLE `plans` 
ADD COLUMN `max_products` INT UNSIGNED NULL 
COMMENT 'Máximo de produtos permitidos' 
AFTER `limits`;

-- ================================================================
-- PASSO 2: Adicionar max_orders_per_month
-- ================================================================
ALTER TABLE `plans` 
ADD COLUMN `max_orders_per_month` INT UNSIGNED NULL 
COMMENT 'Máximo de pedidos por mês' 
AFTER `max_products`;

-- ================================================================
-- PASSO 3: Adicionar max_users
-- ================================================================
ALTER TABLE `plans` 
ADD COLUMN `max_users` INT UNSIGNED NULL 
COMMENT 'Máximo de usuários permitidos' 
AFTER `max_orders_per_month`;

-- ================================================================
-- PASSO 4: Adicionar trial_days (pode já existir)
-- ================================================================
ALTER TABLE `plans` 
ADD COLUMN `trial_days` INT UNSIGNED DEFAULT 14 
COMMENT 'Dias de período de teste' 
AFTER `max_users`;

-- ================================================================
-- PASSO 5: Adicionar billing_cycle
-- ================================================================
ALTER TABLE `plans` 
ADD COLUMN `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly' 
COMMENT 'Ciclo de cobrança' 
AFTER `trial_days`;

-- ================================================================
-- VERIFICAÇÃO: Mostrar estrutura da tabela
-- ================================================================
DESCRIBE `plans`;
