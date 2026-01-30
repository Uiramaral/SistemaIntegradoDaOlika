-- ============================================
-- RASTREAMENTO DE ENTREGAS EM TEMPO REAL
-- Data: 2026-01-27
-- Descrição: Sistema de tracking GPS com mapa interno
-- ============================================

-- 1. Criar tabela delivery_tracking
CREATE TABLE IF NOT EXISTS `delivery_tracking` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID do entregador',
  `latitude` decimal(10,7) NOT NULL COMMENT 'Latitude GPS',
  `longitude` decimal(10,7) NOT NULL COMMENT 'Longitude GPS',
  `accuracy` decimal(8,2) DEFAULT NULL COMMENT 'Precisão em metros',
  `speed` decimal(8,2) DEFAULT NULL COMMENT 'Velocidade em km/h',
  `heading` decimal(5,2) DEFAULT NULL COMMENT 'Direção em graus (0-360)',
  `tracked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento da captura GPS',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_tracked` (`order_id`, `tracked_at`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_delivery_tracking_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delivery_tracking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de localizações GPS do entregador';

-- 2. Adicionar colunas de rastreamento na tabela orders
ALTER TABLE `orders` 
ADD COLUMN `tracking_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Se rastreamento está ativo' AFTER `delivery_instructions`,
ADD COLUMN `tracking_started_at` timestamp NULL DEFAULT NULL COMMENT 'Quando rastreamento iniciou' AFTER `tracking_enabled`,
ADD COLUMN `tracking_stopped_at` timestamp NULL DEFAULT NULL COMMENT 'Quando rastreamento parou' AFTER `tracking_started_at`,
ADD COLUMN `tracking_token` varchar(64) DEFAULT NULL COMMENT 'Token único para link público' AFTER `tracking_stopped_at`,
ADD UNIQUE KEY `idx_tracking_token` (`tracking_token`);

-- 3. Adicionar índice para melhor performance em consultas de tracking ativo
ALTER TABLE `orders` 
ADD INDEX `idx_tracking_status` (`tracking_enabled`, `status`);

-- ============================================
-- VERIFICAÇÃO
-- ============================================
-- SELECT 
--   TABLE_NAME, 
--   COLUMN_NAME, 
--   COLUMN_TYPE, 
--   COLUMN_COMMENT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
--   AND TABLE_NAME IN ('orders', 'delivery_tracking')
--   AND COLUMN_NAME LIKE '%tracking%'
-- ORDER BY TABLE_NAME, ORDINAL_POSITION;
