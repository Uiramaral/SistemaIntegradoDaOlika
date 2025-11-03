-- Criar tabela para registro de transações de cashback
CREATE TABLE IF NOT EXISTS `customer_cashback` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor positivo para crédito (ganho), negativo para débito (uso)',
  `type` ENUM('credit', 'debit') NOT NULL DEFAULT 'credit' COMMENT 'credit = ganho de cashback, debit = uso de cashback',
  `description` VARCHAR(255) NULL COMMENT 'Descrição da transação',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_type` (`type`),
  CONSTRAINT `fk_cashback_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cashback_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

