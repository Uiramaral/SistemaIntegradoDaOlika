-- Cria tabelas de ingredientes e vínculo com produtos (idempotente)
CREATE TABLE IF NOT EXISTS `ingredients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ingredients_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_ingredient` (
  `product_id` BIGINT UNSIGNED NOT NULL,
  `ingredient_id` INT UNSIGNED NOT NULL,
  `percentage` DECIMAL(8,2) NULL,
  PRIMARY KEY (`product_id`, `ingredient_id`),
  KEY `idx_pi_ingredient` (`ingredient_id`),
  CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
