-- Tabela para faixas dinâmicas de distância de entrega
CREATE TABLE IF NOT EXISTS `delivery_fee_ranges` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_fee_id` bigint UNSIGNED NOT NULL,
  `min_distance_km` decimal(8,2) NOT NULL DEFAULT 0.00,
  `max_distance_km` decimal(8,2) NULL DEFAULT NULL,
  `fee_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `fee_type` enum('fixed','per_km') NOT NULL DEFAULT 'fixed',
  `delivery_time_minutes` int UNSIGNED NULL DEFAULT NULL,
  `order` int UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_delivery_fee` (`delivery_fee_id`),
  KEY `idx_min_distance` (`min_distance_km`),
  KEY `idx_max_distance` (`max_distance_km`),
  CONSTRAINT `fk_delivery_fee_ranges_delivery_fee` FOREIGN KEY (`delivery_fee_id`) REFERENCES `delivery_fees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

