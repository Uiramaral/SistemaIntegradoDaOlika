CREATE TABLE `whatsapp_instances` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT 'Ex: Principal (Vendas), Secundario (Marketing)',
  `phone_number` varchar(20) DEFAULT NULL COMMENT 'Número conectado (após pareamento)',
  `api_url` varchar(255) NOT NULL COMMENT 'URL do Railway (ex: https://olika-wa-01.up.railway.app)',
  `api_token` varchar(255) DEFAULT NULL COMMENT 'Opcional: Token de segurança',
  `status` enum('DISCONNECTED','CONNECTING','CONNECTED') NOT NULL DEFAULT 'DISCONNECTED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

