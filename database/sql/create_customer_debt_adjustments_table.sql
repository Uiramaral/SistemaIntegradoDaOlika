-- Tabela para histórico de ajustes de saldo devedor
CREATE TABLE IF NOT EXISTS `customer_debt_adjustments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `old_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo antes do ajuste',
  `new_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo após o ajuste',
  `adjustment_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor do ajuste (diferença)',
  `reason` TEXT NULL COMMENT 'Motivo do ajuste',
  `created_by` BIGINT UNSIGNED NULL COMMENT 'ID do usuário que fez o ajuste',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_created_at` (`created_at`),
  CONSTRAINT `fk_debt_adjustments_customer` 
    FOREIGN KEY (`customer_id`) 
    REFERENCES `customers` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

