-- Adicionar campos de multiplicadores e custos operacionais na tabela settings
ALTER TABLE `settings` 
ADD COLUMN `sales_multiplier` DECIMAL(5,2) NOT NULL DEFAULT 3.5 COMMENT 'Multiplicador para cálculo de preço de venda' AFTER `cashback_percentage`,
ADD COLUMN `resale_multiplier` DECIMAL(5,2) NOT NULL DEFAULT 2.5 COMMENT 'Multiplicador para cálculo de preço de revenda' AFTER `sales_multiplier`,
ADD COLUMN `fixed_cost` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Custo fixo mensal' AFTER `resale_multiplier`,
ADD COLUMN `tax_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Percentual de imposto' AFTER `fixed_cost`,
ADD COLUMN `card_fee_percentage` DECIMAL(5,2) NOT NULL DEFAULT 6.0 COMMENT 'Percentual de taxa de cartão' AFTER `tax_percentage`;
