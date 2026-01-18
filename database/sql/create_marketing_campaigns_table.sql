-- ========================================
-- CRIAR TABELA DE CAMPANHAS DE MARKETING
-- Data: 2026-01-17
-- Descrição: Sistema de disparo de mensagens WhatsApp para aumentar vendas
-- ========================================

CREATE TABLE IF NOT EXISTS `marketing_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` BIGINT UNSIGNED NULL COMMENT 'Cliente/Estabelecimento',
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome da campanha',
  `description` TEXT NULL COMMENT 'Descrição da campanha',
  `status` ENUM('draft', 'scheduled', 'running', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft' COMMENT 'Status da campanha',
  
  -- Templates de mensagem (A/B/C testing)
  `message_template_a` TEXT NOT NULL COMMENT 'Template de mensagem A',
  `message_template_b` TEXT NULL COMMENT 'Template de mensagem B (opcional)',
  `message_template_c` TEXT NULL COMMENT 'Template de mensagem C (opcional)',
  `use_ab_testing` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Usar variações A/B/C',
  
  -- Filtros de audiência
  `target_filter` JSON NULL COMMENT 'Filtros de segmentação: {min_orders, max_orders, has_cashback, min_cashback, customer_ids, etc}',
  `target_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total de destinatários',
  
  -- Agendamento
  `scheduled_at` DATETIME NULL COMMENT 'Data/hora agendada para envio',
  `send_immediately` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Enviar imediatamente',
  `interval_seconds` INT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Intervalo entre envios (segundos)',
  
  -- Estatísticas
  `sent_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Mensagens enviadas',
  `delivered_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Mensagens entregues',
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Falhas no envio',
  `started_at` DATETIME NULL COMMENT 'Início real do envio',
  `completed_at` DATETIME NULL COMMENT 'Conclusão do envio',
  
  -- Metadados
  `created_by` BIGINT UNSIGNED NULL COMMENT 'Usuário que criou',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  INDEX `idx_client_id` (`client_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_scheduled_at` (`scheduled_at`),
  CONSTRAINT `fk_marketing_campaigns_client` 
    FOREIGN KEY (`client_id`) 
    REFERENCES `clients` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- CRIAR TABELA DE LOGS DE ENVIO
-- ========================================

CREATE TABLE IF NOT EXISTS `marketing_campaign_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT UNSIGNED NOT NULL COMMENT 'Campanha relacionada',
  `customer_id` BIGINT UNSIGNED NULL COMMENT 'Cliente destinatário',
  `phone` VARCHAR(20) NOT NULL COMMENT 'Telefone do destinatário',
  `customer_name` VARCHAR(255) NULL COMMENT 'Nome do cliente',
  
  -- Mensagem enviada
  `message_sent` TEXT NOT NULL COMMENT 'Mensagem enviada (já processada)',
  `template_version` ENUM('A', 'B', 'C') NOT NULL DEFAULT 'A' COMMENT 'Versão do template usada',
  
  -- Status do envio
  `status` ENUM('pending', 'sent', 'delivered', 'failed', 'read') NOT NULL DEFAULT 'pending',
  `error_message` TEXT NULL COMMENT 'Mensagem de erro (se houver)',
  `whatsapp_message_id` VARCHAR(255) NULL COMMENT 'ID da mensagem no WhatsApp',
  
  -- Timestamps
  `sent_at` DATETIME NULL COMMENT 'Data/hora do envio',
  `delivered_at` DATETIME NULL COMMENT 'Data/hora da entrega',
  `read_at` DATETIME NULL COMMENT 'Data/hora da leitura',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  INDEX `idx_campaign_id` (`campaign_id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_phone` (`phone`),
  CONSTRAINT `fk_campaign_logs_campaign` 
    FOREIGN KEY (`campaign_id`) 
    REFERENCES `marketing_campaigns` (`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `fk_campaign_logs_customer` 
    FOREIGN KEY (`customer_id`) 
    REFERENCES `customers` (`id`) 
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- VERIFICAÇÃO
-- ========================================
SELECT 
    'Tabelas de campanhas de marketing criadas com sucesso!' as status,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'marketing_campaigns') as marketing_campaigns_exists,
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'marketing_campaign_logs') as campaign_logs_exists;
