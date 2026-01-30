-- Tabela Finanças: receitas e despesas (módulo Finanças)
-- Requer tabela `clients`. Execute após ter a estrutura multi-tenant.

CREATE TABLE IF NOT EXISTS `financial_transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NULL COMMENT 'Multi-tenant: estabelecimento',
  `type` ENUM('revenue','expense') NOT NULL COMMENT 'revenue=receita, expense=despesa',
  `amount` DECIMAL(12,2) NOT NULL,
  `description` VARCHAR(500) NULL,
  `transaction_date` DATE NOT NULL,
  `category` VARCHAR(64) NULL,
  `order_id` BIGINT UNSIGNED NULL COMMENT 'Pedido que originou a receita (quando tipo=revenue)',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_financial_transactions_client_id` (`client_id`),
  INDEX `idx_financial_transactions_client_type` (`client_id`, `type`),
  INDEX `idx_financial_transactions_client_date` (`client_id`, `transaction_date`),
  INDEX `idx_financial_transactions_order_id` (`order_id`),
  CONSTRAINT `fk_financial_transactions_client`
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
