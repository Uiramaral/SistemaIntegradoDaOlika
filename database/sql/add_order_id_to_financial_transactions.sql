-- Adiciona order_id em financial_transactions (receita automática de pedidos pagos)
-- Execute se a tabela financial_transactions já existir sem a coluna.

ALTER TABLE `financial_transactions`
  ADD COLUMN `order_id` BIGINT UNSIGNED NULL COMMENT 'Pedido que originou a receita' AFTER `category`,
  ADD INDEX `idx_financial_transactions_order_id` (`order_id`);

-- FK opcional: descomente se a tabela orders existir e quiser integridade referencial
-- ALTER TABLE `financial_transactions`
--   ADD CONSTRAINT `fk_financial_transactions_order`
--     FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;
