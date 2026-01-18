-- =============================================================================
-- MIGRAÇÃO: Sistema de Planos, Assinaturas e Instâncias WhatsApp
-- Data: 2024-12-18
-- Descrição: Cria tabelas para gerenciamento de planos SaaS, assinaturas e
--            URLs de instâncias WhatsApp do Railway
-- =============================================================================

-- =============================================================================
-- TABELA: plans (Planos disponíveis)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `plans` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'Nome do plano (ex: Básico, WhatsApp, WhatsApp + I.A.)',
    `slug` VARCHAR(100) NOT NULL COMMENT 'Slug único para identificação',
    `description` TEXT NULL COMMENT 'Descrição do plano',
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Preço mensal do plano',
    `features` JSON NULL COMMENT 'Lista de funcionalidades incluídas',
    `limits` JSON NULL COMMENT 'Limites do plano (produtos, pedidos, etc.)',
    `has_whatsapp` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se inclui WhatsApp',
    `has_ai` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se inclui I.A.',
    `max_whatsapp_instances` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Máx. instâncias WhatsApp incluídas',
    `whatsapp_instance_price` DECIMAL(10,2) NOT NULL DEFAULT 15.00 COMMENT 'Preço por instância adicional',
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Se está ativo para novos clientes',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se é destacado na página de planos',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: subscriptions (Assinaturas dos clientes)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` BIGINT UNSIGNED NOT NULL COMMENT 'Cliente/Estabelecimento',
    `plan_id` BIGINT UNSIGNED NOT NULL COMMENT 'Plano contratado',
    `status` ENUM('active', 'pending', 'cancelled', 'expired', 'suspended') NOT NULL DEFAULT 'pending' COMMENT 'Status da assinatura',
    `price` DECIMAL(10,2) NOT NULL COMMENT 'Preço da assinatura (pode ter desconto)',
    `started_at` TIMESTAMP NULL COMMENT 'Data de início da assinatura',
    `current_period_start` TIMESTAMP NULL COMMENT 'Início do período atual',
    `current_period_end` TIMESTAMP NULL COMMENT 'Fim do período atual (próxima renovação)',
    `cancelled_at` TIMESTAMP NULL COMMENT 'Data de cancelamento',
    `cancellation_reason` TEXT NULL COMMENT 'Motivo do cancelamento',
    `trial_ends_at` TIMESTAMP NULL COMMENT 'Fim do período de trial',
    `notes` TEXT NULL COMMENT 'Observações internas',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `subscriptions_client_id_index` (`client_id`),
    KEY `subscriptions_plan_id_index` (`plan_id`),
    KEY `subscriptions_status_index` (`status`),
    KEY `subscriptions_current_period_end_index` (`current_period_end`),
    CONSTRAINT `subscriptions_client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
    CONSTRAINT `subscriptions_plan_id_fk` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: subscription_addons (Adicionais da assinatura - instâncias WhatsApp, etc.)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `subscription_addons` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NOT NULL COMMENT 'Assinatura relacionada',
    `addon_type` ENUM('whatsapp_instance', 'ai_credits', 'storage', 'custom') NOT NULL COMMENT 'Tipo de adicional',
    `description` VARCHAR(255) NULL COMMENT 'Descrição do adicional',
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Quantidade',
    `unit_price` DECIMAL(10,2) NOT NULL COMMENT 'Preço unitário',
    `total_price` DECIMAL(10,2) NOT NULL COMMENT 'Preço total',
    `prorated_price` DECIMAL(10,2) NULL COMMENT 'Preço proporcional (se adicionado no meio do período)',
    `started_at` TIMESTAMP NULL COMMENT 'Data de início',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `subscription_addons_subscription_id_index` (`subscription_id`),
    KEY `subscription_addons_addon_type_index` (`addon_type`),
    CONSTRAINT `subscription_addons_subscription_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: subscription_invoices (Faturas/Cobranças)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `subscription_invoices` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NOT NULL,
    `invoice_number` VARCHAR(50) NOT NULL COMMENT 'Número da fatura',
    `amount` DECIMAL(10,2) NOT NULL COMMENT 'Valor total',
    `status` ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending',
    `due_date` DATE NOT NULL COMMENT 'Data de vencimento',
    `paid_at` TIMESTAMP NULL COMMENT 'Data do pagamento',
    `payment_method` VARCHAR(50) NULL COMMENT 'Método de pagamento',
    `payment_reference` VARCHAR(255) NULL COMMENT 'Referência do pagamento (ID transação)',
    `items` JSON NULL COMMENT 'Itens da fatura (plano + adicionais)',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `subscription_invoices_number_unique` (`invoice_number`),
    KEY `subscription_invoices_subscription_id_index` (`subscription_id`),
    KEY `subscription_invoices_status_index` (`status`),
    CONSTRAINT `subscription_invoices_subscription_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: whatsapp_instance_urls (URLs de instâncias WhatsApp no Railway)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `whatsapp_instance_urls` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `url` VARCHAR(500) NOT NULL COMMENT 'URL da instância no Railway',
    `name` VARCHAR(100) NOT NULL COMMENT 'Nome identificador da instância',
    `status` ENUM('available', 'assigned', 'maintenance', 'offline') NOT NULL DEFAULT 'available',
    `client_id` BIGINT UNSIGNED NULL COMMENT 'Cliente que está usando (se assigned)',
    `whatsapp_instance_id` BIGINT UNSIGNED NULL COMMENT 'Instância WhatsApp vinculada',
    `railway_service_id` VARCHAR(255) NULL COMMENT 'ID do serviço no Railway',
    `railway_project_id` VARCHAR(255) NULL COMMENT 'ID do projeto no Railway',
    `max_connections` INT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Máx. conexões simultâneas',
    `current_connections` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Conexões atuais',
    `last_health_check` TIMESTAMP NULL COMMENT 'Último health check',
    `health_status` ENUM('healthy', 'unhealthy', 'unknown') NOT NULL DEFAULT 'unknown',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `whatsapp_instance_urls_url_unique` (`url`),
    KEY `whatsapp_instance_urls_status_index` (`status`),
    KEY `whatsapp_instance_urls_client_id_index` (`client_id`),
    CONSTRAINT `whatsapp_instance_urls_client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: subscription_notifications (Notificações de vencimento)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `subscription_notifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `subscription_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('expiring_soon', 'expired', 'payment_failed', 'payment_received', 'plan_changed') NOT NULL,
    `days_before_expiry` INT NULL COMMENT 'Dias antes do vencimento (para expiring_soon)',
    `sent_at` TIMESTAMP NULL COMMENT 'Data de envio',
    `channel` ENUM('email', 'whatsapp', 'push', 'in_app') NOT NULL DEFAULT 'in_app',
    `message` TEXT NULL,
    `read_at` TIMESTAMP NULL COMMENT 'Data de leitura (para in_app)',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `subscription_notifications_subscription_id_index` (`subscription_id`),
    KEY `subscription_notifications_type_index` (`type`),
    CONSTRAINT `subscription_notifications_subscription_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- TABELA: master_settings (Configurações do Dashboard Master)
-- =============================================================================
CREATE TABLE IF NOT EXISTS `master_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL COMMENT 'Chave da configuração',
    `value` TEXT NULL COMMENT 'Valor',
    `type` ENUM('string', 'integer', 'decimal', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `description` VARCHAR(255) NULL COMMENT 'Descrição da configuração',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `master_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DADOS INICIAIS: Planos de exemplo
-- =============================================================================
INSERT INTO `plans` (`name`, `slug`, `description`, `price`, `features`, `limits`, `has_whatsapp`, `has_ai`, `max_whatsapp_instances`, `whatsapp_instance_price`, `sort_order`, `is_active`, `is_featured`) VALUES
('Básico', 'basico', 'Ideal para pequenos estabelecimentos', 49.90, 
    '["Cardápio digital ilimitado", "Gestão de pedidos", "Relatórios básicos", "Suporte por email"]',
    '{"products": 100, "orders_per_month": 500, "users": 2}',
    0, 0, 0, 15.00, 1, 1, 0),
    
('WhatsApp', 'whatsapp', 'Perfeito para quem quer automatizar atendimento', 99.90,
    '["Tudo do plano Básico", "1 número WhatsApp", "Notificações automáticas", "Campanhas de marketing", "Suporte prioritário"]',
    '{"products": 500, "orders_per_month": 2000, "users": 5}',
    1, 0, 1, 15.00, 2, 1, 1),
    
('WhatsApp + I.A.', 'whatsapp-ia', 'O mais completo para escalar seu negócio', 199.90,
    '["Tudo do plano WhatsApp", "Atendimento com I.A.", "Respostas automáticas inteligentes", "Análise de sentimento", "Sugestões de produtos", "Suporte VIP"]',
    '{"products": -1, "orders_per_month": -1, "users": -1}',
    1, 1, 3, 15.00, 3, 1, 0);

-- =============================================================================
-- DADOS INICIAIS: Configurações do Master
-- =============================================================================
INSERT INTO `master_settings` (`key`, `value`, `type`, `description`) VALUES
('whatsapp_instance_price', '15.00', 'decimal', 'Preço mensal por instância WhatsApp adicional'),
('trial_days', '7', 'integer', 'Dias de período de teste'),
('expiry_notification_days', '7,3,1', 'string', 'Dias antes do vencimento para enviar notificações'),
('default_plan_slug', 'basico', 'string', 'Slug do plano padrão para novos clientes'),
('billing_currency', 'BRL', 'string', 'Moeda padrão para cobranças')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- =============================================================================
-- ADICIONAR coluna plan_id na tabela clients (se não existir)
-- =============================================================================
SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'subscription_id'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `clients` ADD COLUMN `subscription_id` BIGINT UNSIGNED NULL COMMENT ''Assinatura ativa'' AFTER `active`',
    'SELECT ''Column subscription_id already exists'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar a tabela whatsapp_instances para vincular com whatsapp_instance_urls
SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'whatsapp_instances' 
    AND COLUMN_NAME = 'instance_url_id'
);

SET @sql = IF(@column_exists = 0, 
    'ALTER TABLE `whatsapp_instances` ADD COLUMN `instance_url_id` BIGINT UNSIGNED NULL COMMENT ''URL da instância no Railway'' AFTER `api_url`',
    'SELECT ''Column instance_url_id already exists'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =============================================================================
-- ÍNDICES DE PERFORMANCE (MySQL não suporta IF NOT EXISTS para CREATE INDEX)
-- Execute separadamente se precisar recriar os índices
-- =============================================================================

-- Criar índice para assinaturas expirando (ignorar erro se já existir)
SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'subscriptions' AND INDEX_NAME = 'idx_subscriptions_expiring');
SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX `idx_subscriptions_expiring` ON `subscriptions` (`status`, `current_period_end`)',
    'SELECT ''Index idx_subscriptions_expiring already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Criar índice para planos ativos/destacados
SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND INDEX_NAME = 'idx_plans_active_featured');
SET @sql = IF(@index_exists = 0, 
    'CREATE INDEX `idx_plans_active_featured` ON `plans` (`is_active`, `is_featured`, `sort_order`)',
    'SELECT ''Index idx_plans_active_featured already exists''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
