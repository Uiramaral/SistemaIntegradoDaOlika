-- Criação da tabela order_ratings para avaliação de pedidos pelos clientes
-- Data: 2025-01-15

CREATE TABLE IF NOT EXISTS `order_ratings` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` BIGINT(20) UNSIGNED NOT NULL,
    `customer_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
    `rating` TINYINT(3) UNSIGNED NOT NULL COMMENT 'Avaliação de 1 a 5 estrelas',
    `comment` TEXT NULL DEFAULT NULL COMMENT 'Comentário opcional do cliente',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_ratings_order_id_unique` (`order_id`),
    KEY `order_ratings_customer_id_created_at_index` (`customer_id`, `created_at`),
    CONSTRAINT `order_ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_ratings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionais para melhor performance
CREATE INDEX `idx_order_ratings_order_id` ON `order_ratings` (`order_id`);
CREATE INDEX `idx_order_ratings_customer_id` ON `order_ratings` (`customer_id`);
CREATE INDEX `idx_order_ratings_rating` ON `order_ratings` (`rating`);

