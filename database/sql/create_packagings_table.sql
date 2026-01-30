-- Criar tabela de embalagens
CREATE TABLE IF NOT EXISTS `packagings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `cost` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_packagings_client_id` (`client_id`),
  KEY `idx_packagings_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
