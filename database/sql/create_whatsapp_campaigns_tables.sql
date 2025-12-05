-- Tabela de Campanhas
CREATE TABLE IF NOT EXISTS `whatsapp_campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_audience` varchar(255) NOT NULL DEFAULT 'all',
  `interval_seconds` int(11) NOT NULL DEFAULT 10,
  `total_leads` int(11) NOT NULL DEFAULT 0,
  `processed_count` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Logs de Envio (Detalhes de cada mensagem)
CREATE TABLE IF NOT EXISTS `whatsapp_campaign_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NULL,
  `phone` varchar(50) NOT NULL,
  `whatsapp_instance_id` bigint(20) UNSIGNED NULL,
  `status` varchar(50) NOT NULL,
  `error` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_campaign_logs_campaign_id_foreign` (`campaign_id`),
  KEY `whatsapp_campaign_logs_customer_id_foreign` (`customer_id`),
  CONSTRAINT `whatsapp_campaign_logs_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `whatsapp_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `whatsapp_campaign_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;









