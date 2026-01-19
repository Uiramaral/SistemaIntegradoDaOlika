-- ========================================
-- CRIAR TABELA DE INTEGRAÇÕES DE APIs
-- Data: 2026-01-17
-- Descrição: Centralizador de configurações de APIs externas (Gemini, OpenAI, MercadoPago, etc)
-- ========================================

CREATE TABLE IF NOT EXISTS `api_integrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NULL COMMENT 'Cliente/Estabelecimento (multi-tenant)',
  `provider` VARCHAR(50) NOT NULL COMMENT 'Nome do provedor: gemini, openai, mercadopago, pagseguro, whatsapp, etc',
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'API ativada/desativada',
  
  -- Credenciais (JSON para flexibilidade)
  `credentials` JSON NULL COMMENT 'Credenciais da API: {api_key, secret, token, etc}',
  
  -- Configurações específicas (JSON)
  `settings` JSON NULL COMMENT 'Configurações adicionais: {model, temperature, webhook_url, etc}',
  
  -- Metadados
  `last_tested_at` DATETIME NULL COMMENT 'Última vez que testou conexão',
  `last_test_status` ENUM('success', 'failed', 'pending') NULL COMMENT 'Resultado do último teste',
  `last_error` TEXT NULL COMMENT 'Última mensagem de erro',
  
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  INDEX `idx_client_provider` (`client_id`, `provider`),
  INDEX `idx_provider` (`provider`),
  UNIQUE KEY `unique_client_provider` (`client_id`, `provider`),
  CONSTRAINT `fk_api_integrations_client` 
    FOREIGN KEY (`client_id`) 
    REFERENCES `clients` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INSERIR PROVIDERS PADRÃO (templates)
-- ========================================

-- Nota: Cada estabelecimento terá suas próprias configurações
-- Estes são apenas exemplos de estrutura

INSERT IGNORE INTO `api_integrations` 
(`client_id`, `provider`, `is_enabled`, `credentials`, `settings`) 
VALUES
(NULL, 'gemini', 0, 
  '{"api_key": ""}', 
  '{"model": "gemini-2.5-flash", "temperature": 0.7, "max_tokens": 500}'
),
(NULL, 'openai', 0, 
  '{"api_key": ""}', 
  '{"model": "gpt-3.5-turbo", "temperature": 0.7, "max_tokens": 500}'
),
(NULL, 'mercadopago', 0, 
  '{"access_token": "", "public_key": ""}', 
  '{"webhook_url": "", "notification_url": ""}'
),
(NULL, 'pagseguro', 0, 
  '{"email": "", "token": ""}', 
  '{"sandbox": false}'
),
(NULL, 'whatsapp_evolution', 0, 
  '{"api_url": "", "api_key": "", "instance_name": ""}', 
  '{"sender_name": "Olika Bot"}'
);

-- ========================================
-- VERIFICAÇÃO
-- ========================================
SELECT 
    'Tabela de integrações criada com sucesso!' as status,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'api_integrations') as table_exists,
    (SELECT COUNT(*) FROM `api_integrations`) as default_providers_count;
