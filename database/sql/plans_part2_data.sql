-- =============================================================================
-- PARTE 2: DADOS INICIAIS E ALTERAÇÕES
-- Execute APÓS a Parte 1
-- =============================================================================

-- DADOS INICIAIS: Planos de exemplo
INSERT IGNORE INTO `plans` (`name`, `slug`, `description`, `price`, `features`, `limits`, `has_whatsapp`, `has_ai`, `max_whatsapp_instances`, `whatsapp_instance_price`, `sort_order`, `is_active`, `is_featured`) VALUES
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

-- DADOS INICIAIS: Configurações do Master
INSERT INTO `master_settings` (`key`, `value`, `type`, `description`) VALUES
('whatsapp_instance_price', '15.00', 'decimal', 'Preço mensal por instância WhatsApp adicional'),
('trial_days', '7', 'integer', 'Dias de período de teste'),
('expiry_notification_days', '7,3,1', 'string', 'Dias antes do vencimento para enviar notificações'),
('default_plan_slug', 'basico', 'string', 'Slug do plano padrão para novos clientes'),
('billing_currency', 'BRL', 'string', 'Moeda padrão para cobranças')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- =============================================================================
-- PARTE 3: ALTERAÇÕES EM TABELAS EXISTENTES
-- Execute APENAS se as colunas NÃO existirem (verifique antes)
-- =============================================================================

-- Adicionar subscription_id na tabela clients (se não existir)
-- VERIFIQUE PRIMEIRO: SELECT * FROM clients LIMIT 1;
-- Se a coluna subscription_id NÃO existir, execute:
-- ALTER TABLE `clients` ADD COLUMN `subscription_id` BIGINT UNSIGNED NULL COMMENT 'Assinatura ativa' AFTER `active`;

-- Adicionar instance_url_id na tabela whatsapp_instances (se não existir)
-- VERIFIQUE PRIMEIRO: SELECT * FROM whatsapp_instances LIMIT 1;
-- Se a coluna instance_url_id NÃO existir, execute:
-- ALTER TABLE `whatsapp_instances` ADD COLUMN `instance_url_id` BIGINT UNSIGNED NULL COMMENT 'URL da instância no Railway' AFTER `api_url`;

-- =============================================================================
-- Atualizar pedido 138 que está com client_id NULL
-- =============================================================================
UPDATE `orders` SET `client_id` = 1 WHERE `client_id` IS NULL;
