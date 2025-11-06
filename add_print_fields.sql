-- Adicionar campos de controle de impressão na tabela orders
-- Execute este SQL no seu banco de dados

ALTER TABLE `orders` 
ADD COLUMN `print_requested_at` TIMESTAMP NULL DEFAULT NULL AFTER `scheduled_delivery_at`,
ADD COLUMN `printed_at` TIMESTAMP NULL DEFAULT NULL AFTER `print_requested_at`;

-- Adicionar índices para melhor performance
CREATE INDEX `orders_print_requested_at_index` ON `orders` (`print_requested_at`);
CREATE INDEX `orders_printed_at_index` ON `orders` (`printed_at`);


