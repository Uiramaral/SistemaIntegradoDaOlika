-- Tabela para preços diferenciados de produtos para clientes de revenda
CREATE TABLE IF NOT EXISTS `product_wholesale_prices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID do produto',
  `variant_id` BIGINT UNSIGNED NULL COMMENT 'ID da variante (se aplicável)',
  `wholesale_price` DECIMAL(10, 2) NOT NULL COMMENT 'Preço para revenda/restaurantes',
  `min_quantity` INT UNSIGNED NULL DEFAULT 1 COMMENT 'Quantidade mínima para aplicar este preço',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Preço ativo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_product_variant` (`product_id`, `variant_id`),
  INDEX `idx_product_active` (`product_id`, `is_active`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Preços diferenciados para clientes de revenda/restaurantes';

