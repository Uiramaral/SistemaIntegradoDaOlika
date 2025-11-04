-- Tabela para armazenar eventos de analytics
CREATE TABLE IF NOT EXISTS `analytics_events` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_type` VARCHAR(255) NOT NULL COMMENT 'Tipo de evento: page_view, add_to_cart, checkout_started, purchase',
  `page_path` VARCHAR(255) NULL COMMENT 'URL/path da página',
  `session_id` VARCHAR(255) NULL COMMENT 'ID da sessão para rastrear usuários',
  `ip_address` VARCHAR(45) NULL COMMENT 'Endereço IP do visitante',
  `user_agent` TEXT NULL COMMENT 'User agent do navegador',
  `product_id` BIGINT UNSIGNED NULL COMMENT 'ID do produto (para eventos de produto)',
  `order_id` BIGINT UNSIGNED NULL COMMENT 'ID do pedido (para eventos de compra)',
  `customer_id` BIGINT UNSIGNED NULL COMMENT 'ID do cliente identificado',
  `metadata` JSON NULL COMMENT 'Dados extras (ex: quantidade, valor, etc)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora do evento',
  PRIMARY KEY (`id`),
  INDEX `idx_event_type_created_at` (`event_type`, `created_at`),
  INDEX `idx_session_created_at` (`session_id`, `created_at`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_product_id` (`product_id`),
  CONSTRAINT `fk_analytics_events_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_analytics_events_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_analytics_events_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

