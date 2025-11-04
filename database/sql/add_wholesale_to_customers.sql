-- Adicionar campo para identificar clientes de revenda/restaurantes
ALTER TABLE `customers` 
ADD COLUMN `is_wholesale` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Cliente de revenda/restaurante, 0 = Cliente comum' AFTER `is_active`;

-- Criar índice para busca rápida
CREATE INDEX `idx_customers_wholesale` ON `customers` (`is_wholesale`);

