-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 02/02/2026 às 15:24
-- Versão do servidor: 8.0.44-35
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `hg6ddb59_larav25`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `cep` varchar(10) NOT NULL,
  `street` varchar(255) NOT NULL,
  `number` varchar(50) NOT NULL,
  `complement` varchar(255) DEFAULT NULL,
  `neighborhood` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(2) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ai_exceptions`
--

CREATE TABLE `ai_exceptions` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número de telefone (apenas dígitos)',
  `reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo da exceção (ex: image_received, video_received, manual_override)',
  `active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status ativo/inativo',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Data de expiração automática (ex: 5 minutos)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ai_usage_logs`
--

CREATE TABLE `ai_usage_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL COMMENT 'FK para clients',
  `model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Modelo utilizado (gemini-2.5-flash, etc)',
  `task_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'chat' COMMENT 'Tipo de tarefa (chat, marketing, analysis)',
  `input_tokens` int NOT NULL DEFAULT '0' COMMENT 'Tokens de entrada',
  `output_tokens` int NOT NULL DEFAULT '0' COMMENT 'Tokens de saída',
  `cost_usd` decimal(12,8) NOT NULL DEFAULT '0.00000000' COMMENT 'Custo real em USD',
  `cost_brl` decimal(12,6) NOT NULL DEFAULT '0.000000' COMMENT 'Custo real em BRL',
  `charged_brl` decimal(12,6) NOT NULL DEFAULT '0.000000' COMMENT 'Valor cobrado do cliente em BRL (com markup)',
  `profit_brl` decimal(12,6) NOT NULL DEFAULT '0.000000' COMMENT 'Lucro em BRL',
  `prompt_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Preview do prompt (primeiros 255 chars)',
  `response_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Preview da resposta (primeiros 255 chars)',
  `success` tinyint(1) DEFAULT '1' COMMENT 'Se a requisição foi bem-sucedida',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT 'Mensagem de erro se falhou',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de consumo de IA por estabelecimento';

-- --------------------------------------------------------

--
-- Estrutura para tabela `allergens`
--

CREATE TABLE `allergens` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `group_name` varchar(60) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `analytics_events`
--

CREATE TABLE `analytics_events` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de evento: page_view, add_to_cart, checkout_started, purchase',
  `page_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL/path da página',
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID da sessão para rastrear usuários',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Endereço IP do visitante',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'User agent do navegador',
  `product_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID do produto (para eventos de produto)',
  `order_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID do pedido (para eventos de compra)',
  `customer_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID do cliente identificado',
  `metadata` json DEFAULT NULL COMMENT 'Dados extras (ex: quantidade, valor, etc)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora do evento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `api_integrations`
--

CREATE TABLE `api_integrations` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Cliente/Estabelecimento (multi-tenant)',
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome do provedor: gemini, openai, mercadopago, pagseguro, whatsapp, etc',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'API ativada/desativada',
  `credentials` json DEFAULT NULL COMMENT 'Credenciais da API: {api_key, secret, token, etc}',
  `settings` json DEFAULT NULL COMMENT 'Configurações adicionais: {model, temperature, webhook_url, etc}',
  `last_tested_at` datetime DEFAULT NULL COMMENT 'Última vez que testou conexão',
  `last_test_status` enum('success','failed','pending') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Resultado do último teste',
  `last_error` text COLLATE utf8mb4_unicode_ci COMMENT 'Última mensagem de erro',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `token` varchar(80) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cashback_backup_20251218`
--

CREATE TABLE `cashback_backup_20251218` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('credit','manual','bonus') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `status` enum('pending','active','used','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_mode` enum('grid2','list') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'grid2',
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `display_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'grid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clients`
--

CREATE TABLE `clients` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT 'Email do estabelecimento',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Telefone do estabelecimento',
  `slug` varchar(120) NOT NULL COMMENT 'Usado no subdomínio (ex: churrasquinhodoze)',
  `plan` enum('basic','ia') DEFAULT 'basic' COMMENT 'Plano ativo: básico ou IA',
  `instance_url` varchar(255) DEFAULT NULL COMMENT 'URL da instância Railway vinculada',
  `deploy_status` enum('pending','in_progress','completed','failed') DEFAULT 'pending',
  `whatsapp_phone` varchar(20) DEFAULT NULL COMMENT 'Número WhatsApp vinculado (plano IA)',
  `ai_context` text COMMENT 'Contexto/instruções específicas para IA (cardápio, horários, regras de negócio)',
  `ai_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se o cliente tem IA habilitada',
  `ai_safety_level` enum('none','low','medium','high') NOT NULL DEFAULT 'medium' COMMENT 'Nível de segurança para prevenir prompt injection',
  `active` tinyint(1) DEFAULT '1',
  `is_master` tinyint(1) DEFAULT '0' COMMENT 'Indica se é o estabelecimento master do SaaS',
  `is_lifetime_free` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Acesso vitalício gratuito (sem renovação)',
  `lifetime_plan` enum('basic','ia','custom') DEFAULT NULL COMMENT 'Plano do acesso vitalício',
  `lifetime_reason` varchar(255) DEFAULT NULL COMMENT 'Motivo do acesso vitalício (ex: Fundador, Parceiro, Tester)',
  `lifetime_granted_at` timestamp NULL DEFAULT NULL COMMENT 'Data de concessão do acesso vitalício',
  `mercadopago_commission_enabled` tinyint(1) DEFAULT '0' COMMENT 'Habilitar comissão do SaaS por venda via Mercado Pago',
  `mercadopago_commission_amount` decimal(10,2) DEFAULT '0.49' COMMENT 'Valor fixo da comissão por venda (padrão R$ 0,49)',
  `subscription_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Assinatura ativa',
  `is_trial` tinyint(1) DEFAULT '0',
  `trial_started_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notificacao_whatsapp` varchar(20) DEFAULT NULL,
  `ai_balance` decimal(10,6) DEFAULT '10.000000' COMMENT 'Saldo de créditos de IA em BRL',
  `ai_tokens_used` int DEFAULT '0' COMMENT 'Total de tokens consumidos',
  `ai_requests_count` int DEFAULT '0' COMMENT 'Total de requisições de IA',
  `ai_last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Última utilização de IA',
  `mp_access_token` varchar(255) DEFAULT NULL,
  `mp_user_id` bigint DEFAULT NULL,
  `mp_refresh_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Estabelecimentos (tenants) do SaaS - cada um com seu contexto de IA isolado';

-- --------------------------------------------------------

--
-- Estrutura para tabela `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `minimum_amount` decimal(8,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `usage_limit_per_customer` int DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `visibility` enum('public','private','targeted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `first_order_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Apenas para primeiro pedido',
  `free_shipping_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Apenas para frete grátis (quando há frete no pedido)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `target_customer_id` bigint UNSIGNED DEFAULT NULL,
  `private_description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `visitor_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preferred_gateway_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_delivery_fee` decimal(10,2) DEFAULT NULL,
  `custom_delivery_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `total_orders` int NOT NULL DEFAULT '0',
  `total_spent` decimal(10,2) NOT NULL DEFAULT '0.00',
  `last_order_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_balance` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_debts` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo total de débitos pendentes (pagamento postergado)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `is_wholesale` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Cliente de revenda/restaurante, 0 = Cliente comum',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customer_cashback`
--

CREATE TABLE `customer_cashback` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor positivo para crédito (ganho), negativo para débito (uso)',
  `type` enum('credit','debit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'credit' COMMENT 'credit = ganho de cashback, debit = uso de cashback',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descrição da transação',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customer_debts`
--

CREATE TABLE `customer_debts` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('debit','credit') NOT NULL DEFAULT 'debit',
  `status` enum('open','settled') NOT NULL DEFAULT 'open',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customer_debt_adjustments`
--

CREATE TABLE `customer_debt_adjustments` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `old_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo antes do ajuste',
  `new_balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo após o ajuste',
  `adjustment_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Valor do ajuste (diferença)',
  `reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Motivo do ajuste',
  `created_by` bigint UNSIGNED DEFAULT NULL COMMENT 'ID do usuário que fez o ajuste',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customer_tags`
--

CREATE TABLE `customer_tags` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome da tag (ex: Newsletter, VIP, etc)',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6' COMMENT 'Cor da tag em hexadecimal',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descrição opcional da tag',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `customer_tag_pivot`
--

CREATE TABLE `customer_tag_pivot` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `delivery_distance_pricing`
--

CREATE TABLE `delivery_distance_pricing` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `min_km` decimal(8,2) NOT NULL DEFAULT '0.00',
  `max_km` decimal(8,2) NOT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_amount_free` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `delivery_fees`
--

CREATE TABLE `delivery_fees` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `base_fee` decimal(8,2) NOT NULL,
  `fee_per_km` decimal(8,2) NOT NULL,
  `minimum_order_value` decimal(8,2) NOT NULL DEFAULT '0.00',
  `free_delivery_threshold` decimal(8,2) DEFAULT NULL,
  `max_distance_km` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `delivery_time_minutes` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `delivery_rules`
--

CREATE TABLE `delivery_rules` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `city` varchar(120) DEFAULT NULL,
  `neighborhood` varchar(120) DEFAULT NULL,
  `cep_from` char(8) DEFAULT NULL,
  `cep_to` char(8) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_amount_free` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `delivery_schedules`
--

CREATE TABLE `delivery_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_orders` int NOT NULL DEFAULT '50',
  `current_orders` int NOT NULL DEFAULT '0',
  `delivery_lead_time_days` int NOT NULL DEFAULT '1',
  `cutoff_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `delivery_tracking`
--

CREATE TABLE `delivery_tracking` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID do entregador',
  `latitude` decimal(10,7) NOT NULL COMMENT 'Latitude GPS',
  `longitude` decimal(10,7) NOT NULL COMMENT 'Longitude GPS',
  `accuracy` decimal(8,2) DEFAULT NULL COMMENT 'Precisão em metros',
  `speed` decimal(8,2) DEFAULT NULL COMMENT 'Velocidade em km/h',
  `heading` decimal(5,2) DEFAULT NULL COMMENT 'Direção em graus (0-360)',
  `tracked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento da captura GPS',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de localizações GPS do entregador';

-- --------------------------------------------------------

--
-- Estrutura para tabela `deployment_logs`
--

CREATE TABLE `deployment_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `github_run_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('queued','in_progress','success','failure','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'queued',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Multi-tenant: estabelecimento',
  `type` enum('revenue','expense') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'revenue=receita, expense=despesa',
  `amount` decimal(12,2) NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `category` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Pedido que originou a receita',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` decimal(10,2) DEFAULT '0.00' COMMENT 'Peso padrão em gramas',
  `percentage` decimal(8,2) DEFAULT NULL COMMENT 'Porcentagem padrão em receitas',
  `is_flour` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se é farinha',
  `has_hydration` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se tem hidratação',
  `hydration_percentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Porcentagem de hidratação',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Categoria: farinha, outro, etc',
  `package_weight` decimal(10,2) DEFAULT NULL COMMENT 'Peso da embalagem em gramas',
  `cost` decimal(10,2) DEFAULT '0.00' COMMENT 'Custo por unidade/embalagem',
  `cost_history` json DEFAULT NULL COMMENT 'Histórico de custos',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'g' COMMENT 'Unidade: g, kg, ml, l, un',
  `stock` decimal(10,2) DEFAULT '0.00' COMMENT 'Estoque atual',
  `min_stock` decimal(10,2) DEFAULT '0.00' COMMENT 'Estoque mínimo',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `instances`
--

CREATE TABLE `instances` (
  `id` bigint UNSIGNED NOT NULL,
  `url` varchar(255) NOT NULL COMMENT 'URL da instância (ex: https://ai7.menuonline.com.br)',
  `status` enum('free','assigned','maintenance') DEFAULT 'free',
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `loyalty_programs`
--

CREATE TABLE `loyalty_programs` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `points_per_real` decimal(8,2) NOT NULL DEFAULT '1.00',
  `real_per_point` decimal(8,4) NOT NULL DEFAULT '0.0100',
  `minimum_points_to_redeem` int NOT NULL DEFAULT '100',
  `points_expiry_days` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('earned','redeemed','expired','bonus','adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `expires_at` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `marketing_campaigns`
--

CREATE TABLE `marketing_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Cliente/Estabelecimento',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome da campanha',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descrição da campanha',
  `status` enum('draft','scheduled','running','paused','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'Status da campanha',
  `message_template_a` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Template de mensagem A',
  `message_template_b` text COLLATE utf8mb4_unicode_ci COMMENT 'Template de mensagem B (opcional)',
  `message_template_c` text COLLATE utf8mb4_unicode_ci COMMENT 'Template de mensagem C (opcional)',
  `use_ab_testing` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Usar variações A/B/C',
  `target_filter` json DEFAULT NULL COMMENT 'Filtros de segmentação: {min_orders, max_orders, has_cashback, min_cashback, customer_ids, etc}',
  `target_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Total de destinatários',
  `scheduled_at` datetime DEFAULT NULL COMMENT 'Data/hora agendada para envio',
  `send_immediately` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Enviar imediatamente',
  `interval_seconds` int UNSIGNED NOT NULL DEFAULT '5' COMMENT 'Intervalo entre envios (segundos)',
  `sent_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Mensagens enviadas',
  `delivered_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Mensagens entregues',
  `failed_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Falhas no envio',
  `started_at` datetime DEFAULT NULL COMMENT 'Início real do envio',
  `completed_at` datetime DEFAULT NULL COMMENT 'Conclusão do envio',
  `created_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Usuário que criou',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `marketing_campaign_logs`
--

CREATE TABLE `marketing_campaign_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` bigint UNSIGNED NOT NULL COMMENT 'Campanha relacionada',
  `customer_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Cliente destinatário',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Telefone do destinatário',
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome do cliente',
  `message_sent` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mensagem enviada (já processada)',
  `template_version` enum('A','B','C') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A' COMMENT 'Versão do template usada',
  `status` enum('pending','sent','delivered','failed','read') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT 'Mensagem de erro (se houver)',
  `whatsapp_message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID da mensagem no WhatsApp',
  `sent_at` datetime DEFAULT NULL COMMENT 'Data/hora do envio',
  `delivered_at` datetime DEFAULT NULL COMMENT 'Data/hora da entrega',
  `read_at` datetime DEFAULT NULL COMMENT 'Data/hora da leitura',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `master_settings`
--

CREATE TABLE `master_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Chave da configuração',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT 'Valor',
  `type` enum('string','integer','decimal','boolean','json') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descrição da configuração',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `address_id` bigint UNSIGNED DEFAULT NULL,
  `visitor_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cashback_used` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Cashback usado no pedido',
  `cashback_earned` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Cashback gerado pelo pedido',
  `discount_type` enum('percentage','fixed','coupon') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de desconto aplicado',
  `discount_original_value` decimal(10,2) DEFAULT NULL COMMENT 'Valor original do desconto (percentual ou fixo) antes do cálculo',
  `manual_discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo do desconto manual (se aplicado separadamente do cupom)',
  `manual_discount_value` decimal(10,2) DEFAULT NULL COMMENT 'Valor do desconto manual (se aplicado separadamente)',
  `coupon_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_provider` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preference_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_link` text COLLATE utf8mb4_unicode_ci,
  `pix_copy_paste` text COLLATE utf8mb4_unicode_ci,
  `pix_qr_base64` mediumtext COLLATE utf8mb4_unicode_ci,
  `pix_expires_at` datetime DEFAULT NULL,
  `payment_raw_response` mediumtext COLLATE utf8mb4_unicode_ci,
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notified_paid_at` datetime DEFAULT NULL,
  `payment_review_notified_at` datetime DEFAULT NULL,
  `notified_unpaid_at` datetime DEFAULT NULL,
  `delivery_type` enum('pickup','delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pickup',
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `delivery_instructions` text COLLATE utf8mb4_unicode_ci,
  `tracking_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se rastreamento está ativo',
  `tracking_started_at` timestamp NULL DEFAULT NULL COMMENT 'Quando rastreamento iniciou',
  `tracking_stopped_at` timestamp NULL DEFAULT NULL COMMENT 'Quando rastreamento parou',
  `tracking_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token único para link público',
  `estimated_time` int DEFAULT NULL COMMENT 'Tempo estimado em minutos',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `delivery_complement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_neighborhood` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `scheduled_delivery_at` timestamp NULL DEFAULT NULL,
  `print_requested_at` timestamp NULL DEFAULT NULL,
  `printed_at` timestamp NULL DEFAULT NULL,
  `print_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'normal' COMMENT 'Tipo de recibo: normal (com preços) ou check (sem preços)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_delivery_fees`
--

CREATE TABLE `order_delivery_fees` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `delivery_fee_id` bigint UNSIGNED DEFAULT NULL,
  `calculated_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `final_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `distance_km` decimal(8,2) DEFAULT NULL,
  `order_value` decimal(8,2) NOT NULL,
  `is_free_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `is_manual_adjustment` tinyint(1) NOT NULL DEFAULT '0',
  `adjustment_reason` text COLLATE utf8mb4_unicode_ci,
  `adjusted_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `custom_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_ratings`
--

CREATE TABLE `order_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `rating` tinyint UNSIGNED NOT NULL COMMENT 'Avaliação de 1 a 5 estrelas',
  `comment` text COLLATE utf8mb4_unicode_ci COMMENT 'Comentário opcional do cliente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_statuses`
--

CREATE TABLE `order_statuses` (
  `id` int NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_final` tinyint(1) DEFAULT '0',
  `notify_customer` tinyint(1) DEFAULT '1',
  `notify_admin` tinyint(1) DEFAULT '0',
  `whatsapp_template_id` int DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `packagings`
--

CREATE TABLE `packagings` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json DEFAULT NULL,
  `pix_qr_base64` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pix_copia_cola` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `payment_settings`
--

CREATE TABLE `payment_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `plans`
--

CREATE TABLE `plans` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome do plano (ex: Básico, WhatsApp, WhatsApp + I.A.)',
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug único para identificação',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descrição do plano',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Preço mensal do plano',
  `features` json DEFAULT NULL COMMENT 'Lista de funcionalidades incluídas',
  `limits` json DEFAULT NULL COMMENT 'Limites do plano (produtos, pedidos, etc.)',
  `max_products` int UNSIGNED DEFAULT NULL COMMENT 'Máximo de produtos permitidos',
  `max_orders_per_month` int UNSIGNED DEFAULT NULL COMMENT 'Máximo de pedidos por mês',
  `max_users` int UNSIGNED DEFAULT NULL COMMENT 'Máximo de usuários permitidos',
  `has_whatsapp` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se inclui WhatsApp',
  `has_ai` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se inclui I.A.',
  `max_whatsapp_instances` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Máx. instâncias WhatsApp incluídas',
  `whatsapp_instance_price` decimal(10,2) NOT NULL DEFAULT '15.00' COMMENT 'Preço por instância adicional',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Ordem de exibição',
  `trial_days` int NOT NULL DEFAULT '0',
  `billing_cycle` enum('monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly' COMMENT 'Ciclo de cobrança (sempre mensal por enquanto)',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se é destacado na página de planos',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `production_lists`
--

CREATE TABLE `production_lists` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `production_date` date NOT NULL,
  `status` enum('draft','active','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `production_list_items`
--

CREATE TABLE `production_list_items` (
  `id` bigint UNSIGNED NOT NULL,
  `production_list_id` bigint UNSIGNED NOT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `recipe_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome da receita no momento da adição',
  `quantity` int NOT NULL DEFAULT '1',
  `weight` decimal(10,2) NOT NULL COMMENT 'Peso unitário em gramas',
  `is_produced` tinyint(1) NOT NULL DEFAULT '0',
  `produced_at` timestamp NULL DEFAULT NULL,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `mark_for_print` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Incluir na fila de impressão; padrão true',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_item_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `production_records`
--

CREATE TABLE `production_records` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `recipe_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome da receita no momento da produção',
  `quantity` int NOT NULL DEFAULT '1' COMMENT 'Quantidade produzida',
  `weight` decimal(10,2) NOT NULL COMMENT 'Peso unitário em gramas',
  `total_produced` decimal(10,2) NOT NULL COMMENT 'Total produzido (quantity * weight)',
  `production_date` date NOT NULL,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Custo total da produção',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `label_description` text COLLATE utf8mb4_unicode_ci,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `weight_grams` int DEFAULT NULL COMMENT 'Peso aproximado em gramas',
  `uses_baker_percentage` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = usa porcentagem de padeiro (farinha como base), 0 = outro método',
  `stock` int DEFAULT '0',
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `preparation_time` int DEFAULT NULL COMMENT 'Tempo em minutos',
  `allergens` text COLLATE utf8mb4_unicode_ci COMMENT 'Lista de alérgenos',
  `nutritional_info` json DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `variants` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_catalog` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Aparece no catálogo público, 0 = Apenas no PDV',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gluten_free` tinyint(1) NOT NULL DEFAULT '0',
  `contamination_risk` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_allergen`
--

CREATE TABLE `product_allergen` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `allergen_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_allergens_backup_20251218`
--

CREATE TABLE `product_allergens_backup_20251218` (
  `product_id` bigint UNSIGNED NOT NULL,
  `allergen_id` bigint UNSIGNED NOT NULL,
  `present` tinyint(1) NOT NULL DEFAULT '0',
  `may_contain` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_ingredient`
--

CREATE TABLE `product_ingredient` (
  `product_id` bigint UNSIGNED NOT NULL,
  `ingredient_id` int UNSIGNED NOT NULL,
  `percentage` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `product_sales_last_90_days`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `product_sales_last_90_days` (
`product_id` bigint unsigned
,`product_name` varchar(255)
,`total_quantity_sold` decimal(32,0)
,`total_orders` bigint
,`total_revenue` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `weight_grams` int DEFAULT NULL COMMENT 'Peso da variação em gramas',
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_wholesale_prices`
--

CREATE TABLE `product_wholesale_prices` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID do produto',
  `variant_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID da variante (se aplicável)',
  `wholesale_price` decimal(10,2) NOT NULL COMMENT 'Preço para revenda/restaurantes',
  `min_quantity` int UNSIGNED DEFAULT '1' COMMENT 'Quantidade mínima para aplicar este preço',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Preço ativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Preços diferenciados para clientes de revenda/restaurantes';

-- --------------------------------------------------------

--
-- Estrutura para tabela `recipes`
--

CREATE TABLE `recipes` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Produto associado a esta receita',
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_weight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Peso total em gramas',
  `hydration` decimal(5,2) NOT NULL DEFAULT '70.00' COMMENT 'Porcentagem de hidratação',
  `levain` decimal(5,2) NOT NULL DEFAULT '30.00' COMMENT 'Porcentagem de levain',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `use_milk_instead_of_water` tinyint(1) NOT NULL DEFAULT '0',
  `is_fermented` tinyint(1) NOT NULL DEFAULT '1',
  `is_bread` tinyint(1) NOT NULL DEFAULT '1',
  `include_notes_in_print` tinyint(1) NOT NULL DEFAULT '0',
  `uses_baker_percentage` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = usa porcentagem de padeiro (farinha como base), 0 = outro método',
  `packaging_cost` decimal(10,2) NOT NULL DEFAULT '0.50' COMMENT 'Custo de embalagem',
  `packaging_id` bigint UNSIGNED DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL COMMENT 'Preço final de venda',
  `resale_price` decimal(10,2) DEFAULT NULL COMMENT 'Preço de revenda',
  `cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Custo total calculado',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `id` bigint UNSIGNED NOT NULL,
  `recipe_step_id` bigint UNSIGNED NOT NULL,
  `ingredient_id` int UNSIGNED NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ingredient' COMMENT 'ingredient, levain, etc',
  `percentage` decimal(8,2) DEFAULT NULL COMMENT 'Porcentagem em relação à farinha',
  `weight` decimal(10,2) DEFAULT NULL COMMENT 'Peso em gramas (calculado ou fixo)',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `recipe_steps`
--

CREATE TABLE `recipe_steps` (
  `id` bigint UNSIGNED NOT NULL,
  `recipe_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Etapa 1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `referrals`
--

CREATE TABLE `referrals` (
  `id` bigint UNSIGNED NOT NULL,
  `referrer_id` bigint UNSIGNED NOT NULL,
  `referred_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','used','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `reward_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `reward_type` enum('points','cashback','discount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'points',
  `expires_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Olika',
  `theme_brand_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_description` text COLLATE utf8mb4_unicode_ci,
  `business_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_address` text COLLATE utf8mb4_unicode_ci,
  `business_full_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_zip_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_latitude` decimal(10,8) DEFAULT NULL,
  `business_longitude` decimal(11,8) DEFAULT NULL,
  `business_cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#FF6B35',
  `theme_primary_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#f59e0b',
  `theme_secondary_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#8b5cf6',
  `theme_accent_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#10b981',
  `theme_background_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#ffffff',
  `theme_text_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#1f2937',
  `theme_border_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#e5e7eb',
  `theme_font_family` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '''Inter'', -apple-system, BlinkMacSystemFont, sans-serif',
  `theme_border_radius` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '12px',
  `theme_shadow_style` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '0 4px 12px rgba(0,0,0,0.08)',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_favicon_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_delivery_value` decimal(8,2) NOT NULL DEFAULT '0.00',
  `free_delivery_threshold` decimal(8,2) NOT NULL DEFAULT '50.00',
  `delivery_fee_per_km` decimal(8,2) NOT NULL DEFAULT '2.50',
  `max_delivery_distance` decimal(8,2) NOT NULL DEFAULT '15.00',
  `mercadopago_access_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mercadopago_public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mercadopago_env` enum('sandbox','production') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `google_maps_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `openai_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_api_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notificacao_whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notificacao_whatsapp_confirmacao` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `loyalty_points_per_real` decimal(8,2) NOT NULL DEFAULT '1.00',
  `cashback_percentage` decimal(5,2) NOT NULL DEFAULT '5.00',
  `sales_multiplier` decimal(5,2) NOT NULL DEFAULT '3.50' COMMENT 'Multiplicador para cálculo de preço de venda',
  `resale_multiplier` decimal(5,2) NOT NULL DEFAULT '2.50' COMMENT 'Multiplicador para cálculo de preço de revenda',
  `fixed_cost` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Custo fixo mensal',
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Percentual de imposto',
  `card_fee_percentage` decimal(5,2) NOT NULL DEFAULT '6.00' COMMENT 'Percentual de taxa de cartão',
  `order_cutoff_time` time DEFAULT NULL,
  `advance_order_days` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `settings`
--
DELIMITER $$
CREATE TRIGGER `trg_settings_per_client` BEFORE INSERT ON `settings` FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM settings WHERE client_id = NEW.client_id) >= 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cada cliente só pode ter 1 registro em settings.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL COMMENT 'Cliente/Estabelecimento',
  `plan_id` bigint UNSIGNED NOT NULL COMMENT 'Plano contratado',
  `status` enum('active','pending','cancelled','expired','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status da assinatura',
  `price` decimal(10,2) NOT NULL COMMENT 'Preço da assinatura (pode ter desconto)',
  `started_at` timestamp NULL DEFAULT NULL COMMENT 'Data de início da assinatura',
  `current_period_start` timestamp NULL DEFAULT NULL COMMENT 'Início do período atual',
  `current_period_end` timestamp NULL DEFAULT NULL COMMENT 'Fim do período atual (próxima renovação)',
  `cancelled_at` timestamp NULL DEFAULT NULL COMMENT 'Data de cancelamento',
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Motivo do cancelamento',
  `trial_ends_at` timestamp NULL DEFAULT NULL COMMENT 'Fim do período de trial',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observações internas',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `subscription_addons`
--

CREATE TABLE `subscription_addons` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL COMMENT 'Assinatura relacionada',
  `addon_type` enum('whatsapp_instance','ai_credits','storage','custom') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de adicional',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descrição do adicional',
  `quantity` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Quantidade',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Preço unitário',
  `total_price` decimal(10,2) NOT NULL COMMENT 'Preço total',
  `prorated_price` decimal(10,2) DEFAULT NULL COMMENT 'Preço proporcional (se adicionado no meio do período)',
  `started_at` timestamp NULL DEFAULT NULL COMMENT 'Data de início',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `subscription_invoices`
--

CREATE TABLE `subscription_invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número da fatura',
  `amount` decimal(10,2) NOT NULL COMMENT 'Valor total',
  `status` enum('pending','paid','failed','refunded','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` date NOT NULL COMMENT 'Data de vencimento',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT 'Data do pagamento',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Método de pagamento',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referência do pagamento (ID transação)',
  `items` json DEFAULT NULL COMMENT 'Itens da fatura (plano + adicionais)',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `subscription_notifications`
--

CREATE TABLE `subscription_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `subscription_id` bigint UNSIGNED NOT NULL,
  `type` enum('expiring_soon','expired','payment_failed','payment_received','plan_changed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `days_before_expiry` int DEFAULT NULL COMMENT 'Dias antes do vencimento (para expiring_soon)',
  `sent_at` timestamp NULL DEFAULT NULL COMMENT 'Data de envio',
  `channel` enum('email','whatsapp','push','in_app') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_app',
  `message` text COLLATE utf8mb4_unicode_ci,
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Data de leitura (para in_app)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `railway_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('super_admin','admin','manager','operator') COLLATE utf8mb4_unicode_ci DEFAULT 'operator',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `v_ai_monthly_profit`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `v_ai_monthly_profit` (
`month` varchar(7)
,`client_id` bigint unsigned
,`client_name` varchar(120)
,`model` varchar(50)
,`requests_count` bigint
,`total_tokens` decimal(33,0)
,`total_cost_usd` decimal(34,8)
,`total_cost_brl` decimal(34,6)
,`total_charged_brl` decimal(34,6)
,`total_profit_brl` decimal(34,6)
,`profit_margin_percent` decimal(40,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `v_ai_profit_by_client`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `v_ai_profit_by_client` (
`client_id` bigint unsigned
,`client_name` varchar(120)
,`slug` varchar(120)
,`ai_balance` decimal(10,6)
,`ai_tokens_used` int
,`ai_requests_count` int
,`total_input_tokens` decimal(32,0)
,`total_output_tokens` decimal(32,0)
,`total_tokens` decimal(33,0)
,`total_cost_usd` decimal(34,8)
,`total_cost_brl` decimal(34,6)
,`total_charged_brl` decimal(34,6)
,`total_profit_brl` decimal(34,6)
,`requests_count` bigint
,`error_count` decimal(23,0)
,`last_request_at` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `v_ai_saas_totals`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `v_ai_saas_totals` (
`clients_with_ai` bigint
,`total_requests` bigint
,`total_tokens` decimal(33,0)
,`total_cost_usd` decimal(34,8)
,`total_cost_brl` decimal(34,6)
,`total_charged_brl` decimal(34,6)
,`total_profit_brl` decimal(34,6)
,`profit_margin_percent` decimal(40,2)
,`total_errors` decimal(23,0)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_campaigns`
--

CREATE TABLE `whatsapp_campaigns` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_audience` varchar(255) NOT NULL DEFAULT 'all',
  `filter_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `filter_customer_type` enum('all','new_customers','existing_customers') NOT NULL DEFAULT 'all',
  `test_customer_id` bigint UNSIGNED DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `scheduled_time` time DEFAULT NULL,
  `interval_seconds` int NOT NULL DEFAULT '10',
  `total_leads` int NOT NULL DEFAULT '0',
  `processed_count` int NOT NULL DEFAULT '0',
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_campaign_logs`
--

CREATE TABLE `whatsapp_campaign_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `campaign_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `phone` varchar(50) NOT NULL,
  `whatsapp_instance_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `error` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_instances`
--

CREATE TABLE `whatsapp_instances` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Ex: Principal (Vendas), Secundario (Marketing)',
  `phone_number` varchar(20) DEFAULT NULL COMMENT 'Número conectado (após pareamento)',
  `api_url` varchar(255) NOT NULL COMMENT 'URL do Railway (ex: https://olika-wa-01.up.railway.app)',
  `instance_url_id` bigint UNSIGNED DEFAULT NULL COMMENT 'URL da instância no Railway',
  `api_token` varchar(255) DEFAULT NULL COMMENT 'Opcional: Token de segurança',
  `status` enum('DISCONNECTED','CONNECTING','CONNECTED') NOT NULL DEFAULT 'DISCONNECTED',
  `last_error_message` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_instance_urls`
--

CREATE TABLE `whatsapp_instance_urls` (
  `id` bigint UNSIGNED NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL da instância no Railway',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'API Key da instância WhatsApp',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descrição adicional da instância',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nome identificador da instância',
  `status` enum('available','assigned','maintenance','offline') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `client_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Cliente que está usando (se assigned)',
  `whatsapp_instance_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Instância WhatsApp vinculada',
  `railway_service_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do serviço no Railway',
  `railway_project_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do projeto no Railway',
  `max_connections` int UNSIGNED NOT NULL DEFAULT '5' COMMENT 'Máx. conexões simultâneas',
  `current_connections` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Conexões atuais',
  `last_health_check` timestamp NULL DEFAULT NULL COMMENT 'Último health check',
  `health_status` enum('healthy','unhealthy','unknown') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_settings`
--

CREATE TABLE `whatsapp_settings` (
  `id` int NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `instance_name` varchar(100) NOT NULL,
  `api_url` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `sender_name` varchar(100) DEFAULT 'Olika Bot',
  `notificacao_whatsapp` varchar(20) DEFAULT NULL,
  `notificacao_whatsapp_confirmacao` varchar(20) DEFAULT NULL,
  `whatsapp_phone` varchar(20) DEFAULT NULL,
  `default_payment_confirmation_phone` varchar(20) DEFAULT NULL,
  `admin_notification_phone` varchar(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `ai_enabled` tinyint(1) DEFAULT '0',
  `openai_api_key` varchar(255) DEFAULT NULL,
  `openai_model` varchar(100) DEFAULT 'gpt-4.1-mini',
  `ai_system_prompt` text,
  `admin_phone` varchar(32) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `whatsapp_templates`
--

CREATE TABLE `whatsapp_templates` (
  `id` int NOT NULL,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `slug` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`,`city`,`state`),
  ADD KEY `idx_addresses_latlng` (`latitude`,`longitude`),
  ADD KEY `idx_addresses_cep` (`cep`);

--
-- Índices de tabela `ai_exceptions`
--
ALTER TABLE `ai_exceptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_phone_active_expires` (`phone`,`active`,`expires_at`),
  ADD KEY `fk_ai_exceptions_client` (`client_id`);

--
-- Índices de tabela `ai_usage_logs`
--
ALTER TABLE `ai_usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_model` (`model`),
  ADD KEY `idx_task_type` (`task_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_client_created` (`client_id`,`created_at`);

--
-- Índices de tabela `allergens`
--
ALTER TABLE `allergens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_allergens_client` (`client_id`);

--
-- Índices de tabela `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type_created_at` (`event_type`,`created_at`),
  ADD KEY `idx_session_created_at` (`session_id`,`created_at`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_analytics_client_date` (`client_id`,`created_at`);

--
-- Índices de tabela `api_integrations`
--
ALTER TABLE `api_integrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_client_provider` (`client_id`,`provider`),
  ADD KEY `idx_client_provider` (`client_id`,`provider`),
  ADD KEY `idx_provider` (`provider`);

--
-- Índices de tabela `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_tokens_clients` (`client_id`);

--
-- Índices de tabela `cashback_backup_20251218`
--
ALTER TABLE `cashback_backup_20251218`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cashback_customer_id_index` (`customer_id`);

--
-- Índices de tabela `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_is_active_sort_order_index` (`is_active`,`sort_order`),
  ADD KEY `idx_categories_client` (`client_id`,`is_active`,`sort_order`);

--
-- Índices de tabela `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_clients_is_master` (`is_master`),
  ADD KEY `idx_clients_mp_commission` (`mercadopago_commission_enabled`,`active`),
  ADD KEY `idx_is_lifetime_free` (`is_lifetime_free`);

--
-- Índices de tabela `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_target_customer_id_foreign` (`target_customer_id`),
  ADD KEY `idx_coupons_validity` (`is_active`,`starts_at`,`expires_at`),
  ADD KEY `idx_coupons_client_active` (`client_id`,`is_active`,`expires_at`);

--
-- Índices de tabela `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_coupon_customer_order` (`coupon_id`,`customer_id`,`order_id`),
  ADD KEY `idx_coupon_customer` (`coupon_id`,`customer_id`),
  ADD KEY `fk_cu_customer` (`customer_id`),
  ADD KEY `fk_cu_order` (`order_id`);

--
-- Índices de tabela `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_phone_unique` (`phone`),
  ADD UNIQUE KEY `customers_email_unique` (`email`),
  ADD UNIQUE KEY `customers_visitor_id_unique` (`visitor_id`),
  ADD KEY `customers_phone_index` (`phone`),
  ADD KEY `customers_email_index` (`email`),
  ADD KEY `customers_visitor_id_index` (`visitor_id`),
  ADD KEY `idx_customers_wholesale` (`is_wholesale`),
  ADD KEY `idx_customers_total_debts` (`total_debts`),
  ADD KEY `idx_newsletter` (`newsletter`),
  ADD KEY `idx_preferred_gateway_phone` (`preferred_gateway_phone`),
  ADD KEY `idx_customers_client_id` (`client_id`),
  ADD KEY `idx_customers_client_active` (`client_id`,`is_active`),
  ADD KEY `idx_customers_client_phone` (`client_id`,`phone`);

--
-- Índices de tabela `customer_cashback`
--
ALTER TABLE `customer_cashback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_type` (`type`);

--
-- Índices de tabela `customer_debts`
--
ALTER TABLE `customer_debts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_order` (`order_id`);

--
-- Índices de tabela `customer_debt_adjustments`
--
ALTER TABLE `customer_debt_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Índices de tabela `customer_tags`
--
ALTER TABLE `customer_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag_name` (`name`),
  ADD KEY `idx_customer_tags_name` (`name`),
  ADD KEY `idx_customer_tags_client` (`client_id`);

--
-- Índices de tabela `customer_tag_pivot`
--
ALTER TABLE `customer_tag_pivot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_customer_tag` (`customer_id`,`tag_id`),
  ADD KEY `idx_customer_tag_pivot_customer` (`customer_id`),
  ADD KEY `idx_customer_tag_pivot_tag` (`tag_id`);

--
-- Índices de tabela `delivery_distance_pricing`
--
ALTER TABLE `delivery_distance_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_range` (`min_km`,`max_km`),
  ADD KEY `idx_delivery_distance_pricing_client` (`client_id`,`is_active`);

--
-- Índices de tabela `delivery_fees`
--
ALTER TABLE `delivery_fees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_delivery_fees_name` (`name`),
  ADD KEY `idx_delivery_fees_client` (`client_id`,`is_active`);

--
-- Índices de tabela `delivery_rules`
--
ALTER TABLE `delivery_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_neighborhood` (`neighborhood`),
  ADD KEY `idx_cep` (`cep_from`,`cep_to`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_delivery_rules_client` (`client_id`,`is_active`);

--
-- Índices de tabela `delivery_schedules`
--
ALTER TABLE `delivery_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_delivery_schedules_slot` (`day_of_week`,`start_time`,`end_time`),
  ADD KEY `idx_delivery_schedules_client` (`client_id`,`is_active`);

--
-- Índices de tabela `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_tracked` (`order_id`,`tracked_at`),
  ADD KEY `idx_user` (`user_id`);

--
-- Índices de tabela `deployment_logs`
--
ALTER TABLE `deployment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_financial_transactions_client_id` (`client_id`),
  ADD KEY `idx_financial_transactions_client_type` (`client_id`,`type`),
  ADD KEY `idx_financial_transactions_client_date` (`client_id`,`transaction_date`),
  ADD KEY `idx_financial_transactions_order_id` (`order_id`);

--
-- Índices de tabela `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_ingredients_slug` (`slug`),
  ADD KEY `ingredients_client_id_foreign` (`client_id`);

--
-- Índices de tabela `instances`
--
ALTER TABLE `instances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url` (`url`),
  ADD KEY `fk_instances_clients` (`assigned_to`);

--
-- Índices de tabela `loyalty_programs`
--
ALTER TABLE `loyalty_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loyalty_programs_client` (`client_id`,`is_active`);

--
-- Índices de tabela `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loyalty_transactions_customer_id_type_index` (`customer_id`,`type`),
  ADD KEY `loyalty_transactions_order_id_index` (`order_id`),
  ADD KEY `loyalty_transactions_expires_at_index` (`expires_at`);

--
-- Índices de tabela `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_scheduled_at` (`scheduled_at`);

--
-- Índices de tabela `marketing_campaign_logs`
--
ALTER TABLE `marketing_campaign_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_campaign_id` (`campaign_id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_phone` (`phone`);

--
-- Índices de tabela `master_settings`
--
ALTER TABLE `master_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_settings_key_unique` (`key`),
  ADD KEY `idx_master_settings_key` (`key`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD UNIQUE KEY `idx_tracking_token` (`tracking_token`),
  ADD KEY `orders_customer_id_status_index` (`customer_id`,`status`),
  ADD KEY `orders_created_at_status_index` (`created_at`,`status`),
  ADD KEY `orders_visitor_id_index` (`visitor_id`),
  ADD KEY `orders_payment_id_index` (`payment_id`),
  ADD KEY `orders_preference_id_index` (`preference_id`),
  ADD KEY `idx_orders_address_id` (`address_id`),
  ADD KEY `orders_print_requested_at_index` (`print_requested_at`),
  ADD KEY `orders_printed_at_index` (`printed_at`),
  ADD KEY `idx_orders_client_id` (`client_id`),
  ADD KEY `idx_orders_payment_status` (`payment_status`),
  ADD KEY `idx_orders_created_at` (`created_at`),
  ADD KEY `idx_orders_client_status` (`client_id`,`status`,`created_at`),
  ADD KEY `idx_orders_client_payment` (`client_id`,`payment_status`),
  ADD KEY `idx_orders_client_date` (`client_id`,`created_at`),
  ADD KEY `idx_tracking_status` (`tracking_enabled`,`status`);

--
-- Índices de tabela `order_delivery_fees`
--
ALTER TABLE `order_delivery_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_delivery_fees_order_id_index` (`order_id`),
  ADD KEY `order_delivery_fees_delivery_fee_id_index` (`delivery_fee_id`),
  ADD KEY `order_delivery_fees_is_manual_adjustment_index` (`is_manual_adjustment`);

--
-- Índices de tabela `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_index` (`order_id`),
  ADD KEY `order_items_product_id_index` (`product_id`),
  ADD KEY `order_items_variant_id_foreign` (`variant_id`),
  ADD KEY `idx_order_items_product_id` (`product_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Índices de tabela `order_ratings`
--
ALTER TABLE `order_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_ratings_order_id_unique` (`order_id`),
  ADD KEY `order_ratings_customer_id_created_at_index` (`customer_id`,`created_at`),
  ADD KEY `idx_order_ratings_order_id` (`order_id`),
  ADD KEY `idx_order_ratings_customer_id` (`customer_id`),
  ADD KEY `idx_order_ratings_rating` (`rating`);

--
-- Índices de tabela `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_status_active` (`active`),
  ADD KEY `fk_os_tpl` (`whatsapp_template_id`),
  ADD KEY `idx_order_statuses_client` (`client_id`,`active`);

--
-- Índices de tabela `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`,`created_at`);

--
-- Índices de tabela `packagings`
--
ALTER TABLE `packagings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_packagings_client_id` (`client_id`),
  ADD KEY `idx_packagings_is_active` (`is_active`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Índices de tabela `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_client_key` (`client_id`,`key`),
  ADD KEY `idx_payment_settings_client_id` (`client_id`);

--
-- Índices de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Índices de tabela `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plans_slug_unique` (`slug`);

--
-- Índices de tabela `production_lists`
--
ALTER TABLE `production_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_production_lists_client_date` (`client_id`,`production_date`),
  ADD KEY `idx_production_lists_status` (`status`);

--
-- Índices de tabela `production_list_items`
--
ALTER TABLE `production_list_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_production_list_items_list_sort` (`production_list_id`,`sort_order`),
  ADD KEY `idx_production_list_items_recipe` (`recipe_id`),
  ADD KEY `idx_production_list_items_produced` (`is_produced`);

--
-- Índices de tabela `production_records`
--
ALTER TABLE `production_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_production_records_client_date` (`client_id`,`production_date`),
  ADD KEY `idx_production_records_recipe` (`recipe_id`);

--
-- Índices de tabela `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_is_featured_index` (`category_id`,`is_featured`),
  ADD KEY `products_is_available_index` (`is_available`),
  ADD KEY `products_sort_order_index` (`sort_order`),
  ADD KEY `idx_products_client_id` (`client_id`),
  ADD KEY `idx_products_client_active` (`client_id`,`is_active`,`category_id`),
  ADD KEY `idx_products_client_catalog` (`client_id`,`show_in_catalog`,`is_available`);

--
-- Índices de tabela `product_allergen`
--
ALTER TABLE `product_allergen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_allergen_unique` (`product_id`,`allergen_id`),
  ADD KEY `allergen_id` (`allergen_id`);

--
-- Índices de tabela `product_allergens_backup_20251218`
--
ALTER TABLE `product_allergens_backup_20251218`
  ADD PRIMARY KEY (`product_id`,`allergen_id`),
  ADD KEY `allergen_id` (`allergen_id`);

--
-- Índices de tabela `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Índices de tabela `product_ingredient`
--
ALTER TABLE `product_ingredient`
  ADD PRIMARY KEY (`product_id`,`ingredient_id`),
  ADD KEY `idx_pi_ingredient` (`ingredient_id`);

--
-- Índices de tabela `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pv_product_sort_idx` (`product_id`,`sort_order`),
  ADD KEY `pv_sku_idx` (`sku`);

--
-- Índices de tabela `product_wholesale_prices`
--
ALTER TABLE `product_wholesale_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_variant` (`product_id`,`variant_id`),
  ADD KEY `idx_product_active` (`product_id`,`is_active`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Índices de tabela `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipes_client_active` (`client_id`,`is_active`),
  ADD KEY `idx_recipes_category` (`category`),
  ADD KEY `idx_recipes_product` (`product_id`),
  ADD KEY `fk_recipes_packaging` (`packaging_id`),
  ADD KEY `fk_recipes_variant_id` (`variant_id`);

--
-- Índices de tabela `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipe_ingredients_step_sort` (`recipe_step_id`,`sort_order`),
  ADD KEY `idx_recipe_ingredients_ingredient` (`ingredient_id`);

--
-- Índices de tabela `recipe_steps`
--
ALTER TABLE `recipe_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipe_steps_recipe_sort` (`recipe_id`,`sort_order`);

--
-- Índices de tabela `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referrals_code_unique` (`code`),
  ADD KEY `referrals_referrer_id_index` (`referrer_id`),
  ADD KEY `referrals_referred_id_index` (`referred_id`),
  ADD KEY `referrals_code_index` (`code`),
  ADD KEY `referrals_status_index` (`status`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices de tabela `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_settings_client_unique` (`client_id`);

--
-- Índices de tabela `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscriptions_client_id_index` (`client_id`),
  ADD KEY `subscriptions_plan_id_index` (`plan_id`),
  ADD KEY `subscriptions_status_index` (`status`),
  ADD KEY `subscriptions_current_period_end_index` (`current_period_end`);

--
-- Índices de tabela `subscription_addons`
--
ALTER TABLE `subscription_addons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_addons_subscription_id_index` (`subscription_id`),
  ADD KEY `subscription_addons_addon_type_index` (`addon_type`);

--
-- Índices de tabela `subscription_invoices`
--
ALTER TABLE `subscription_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscription_invoices_number_unique` (`invoice_number`),
  ADD KEY `subscription_invoices_subscription_id_index` (`subscription_id`),
  ADD KEY `subscription_invoices_status_index` (`status`);

--
-- Índices de tabela `subscription_notifications`
--
ALTER TABLE `subscription_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_notifications_subscription_id_index` (`subscription_id`),
  ADD KEY `subscription_notifications_type_index` (`type`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_slug_unique` (`slug`),
  ADD KEY `idx_users_client` (`client_id`,`role`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_slug` (`slug`);

--
-- Índices de tabela `whatsapp_campaigns`
--
ALTER TABLE `whatsapp_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_campaign_test_customer` (`test_customer_id`),
  ADD KEY `idx_whatsapp_campaigns_client` (`client_id`,`status`);

--
-- Índices de tabela `whatsapp_campaign_logs`
--
ALTER TABLE `whatsapp_campaign_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `whatsapp_campaign_logs_campaign_id_foreign` (`campaign_id`),
  ADD KEY `whatsapp_campaign_logs_customer_id_foreign` (`customer_id`);

--
-- Índices de tabela `whatsapp_instances`
--
ALTER TABLE `whatsapp_instances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_whatsapp_instances_client` (`client_id`,`status`);

--
-- Índices de tabela `whatsapp_instance_urls`
--
ALTER TABLE `whatsapp_instance_urls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `whatsapp_instance_urls_url_unique` (`url`),
  ADD KEY `whatsapp_instance_urls_status_index` (`status`),
  ADD KEY `whatsapp_instance_urls_client_id_index` (`client_id`);

--
-- Índices de tabela `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_whatsapp_settings_client` (`client_id`);

--
-- Índices de tabela `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_whatsapp_templates_client` (`client_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ai_exceptions`
--
ALTER TABLE `ai_exceptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ai_usage_logs`
--
ALTER TABLE `ai_usage_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `allergens`
--
ALTER TABLE `allergens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `analytics_events`
--
ALTER TABLE `analytics_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_integrations`
--
ALTER TABLE `api_integrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cashback_backup_20251218`
--
ALTER TABLE `cashback_backup_20251218`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customer_cashback`
--
ALTER TABLE `customer_cashback`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customer_debts`
--
ALTER TABLE `customer_debts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customer_debt_adjustments`
--
ALTER TABLE `customer_debt_adjustments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customer_tags`
--
ALTER TABLE `customer_tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `customer_tag_pivot`
--
ALTER TABLE `customer_tag_pivot`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `delivery_distance_pricing`
--
ALTER TABLE `delivery_distance_pricing`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `delivery_fees`
--
ALTER TABLE `delivery_fees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `delivery_rules`
--
ALTER TABLE `delivery_rules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `delivery_schedules`
--
ALTER TABLE `delivery_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `deployment_logs`
--
ALTER TABLE `deployment_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `instances`
--
ALTER TABLE `instances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `loyalty_programs`
--
ALTER TABLE `loyalty_programs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marketing_campaign_logs`
--
ALTER TABLE `marketing_campaign_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `master_settings`
--
ALTER TABLE `master_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `order_delivery_fees`
--
ALTER TABLE `order_delivery_fees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `order_ratings`
--
ALTER TABLE `order_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `order_statuses`
--
ALTER TABLE `order_statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `packagings`
--
ALTER TABLE `packagings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `payment_settings`
--
ALTER TABLE `payment_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `production_lists`
--
ALTER TABLE `production_lists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `production_list_items`
--
ALTER TABLE `production_list_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `production_records`
--
ALTER TABLE `production_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_allergen`
--
ALTER TABLE `product_allergen`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_wholesale_prices`
--
ALTER TABLE `product_wholesale_prices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recipe_steps`
--
ALTER TABLE `recipe_steps`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `subscription_addons`
--
ALTER TABLE `subscription_addons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `subscription_invoices`
--
ALTER TABLE `subscription_invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `subscription_notifications`
--
ALTER TABLE `subscription_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_campaigns`
--
ALTER TABLE `whatsapp_campaigns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_campaign_logs`
--
ALTER TABLE `whatsapp_campaign_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_instances`
--
ALTER TABLE `whatsapp_instances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_instance_urls`
--
ALTER TABLE `whatsapp_instance_urls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Estrutura para view `product_sales_last_90_days`
--
DROP TABLE IF EXISTS `product_sales_last_90_days`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_sales_last_90_days`  AS SELECT `oi`.`product_id` AS `product_id`, `p`.`name` AS `product_name`, coalesce(sum(`oi`.`quantity`),0) AS `total_quantity_sold`, count(distinct `oi`.`order_id`) AS `total_orders`, coalesce(sum(`oi`.`total_price`),0) AS `total_revenue` FROM ((`order_items` `oi` join `orders` `o` on((`oi`.`order_id` = `o`.`id`))) join `products` `p` on((`oi`.`product_id` = `p`.`id`))) WHERE ((`o`.`payment_status` = 'paid') AND (`o`.`created_at` >= (now() - interval 90 day))) GROUP BY `oi`.`product_id`, `p`.`name` ORDER BY `total_quantity_sold` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_ai_monthly_profit`
--
DROP TABLE IF EXISTS `v_ai_monthly_profit`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ai_monthly_profit`  AS SELECT date_format(`l`.`created_at`,'%Y-%m') AS `month`, `c`.`id` AS `client_id`, `c`.`name` AS `client_name`, `l`.`model` AS `model`, count(0) AS `requests_count`, sum((`l`.`input_tokens` + `l`.`output_tokens`)) AS `total_tokens`, sum(`l`.`cost_usd`) AS `total_cost_usd`, sum(`l`.`cost_brl`) AS `total_cost_brl`, sum(`l`.`charged_brl`) AS `total_charged_brl`, sum(`l`.`profit_brl`) AS `total_profit_brl`, round(((sum(`l`.`profit_brl`) / nullif(sum(`l`.`cost_brl`),0)) * 100),2) AS `profit_margin_percent` FROM (`ai_usage_logs` `l` join `clients` `c` on((`c`.`id` = `l`.`client_id`))) WHERE (`l`.`success` = 1) GROUP BY date_format(`l`.`created_at`,'%Y-%m'), `c`.`id`, `c`.`name`, `l`.`model` ORDER BY `month` DESC, `total_profit_brl` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_ai_profit_by_client`
--
DROP TABLE IF EXISTS `v_ai_profit_by_client`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ai_profit_by_client`  AS SELECT `c`.`id` AS `client_id`, `c`.`name` AS `client_name`, `c`.`slug` AS `slug`, `c`.`ai_balance` AS `ai_balance`, `c`.`ai_tokens_used` AS `ai_tokens_used`, `c`.`ai_requests_count` AS `ai_requests_count`, coalesce(sum(`l`.`input_tokens`),0) AS `total_input_tokens`, coalesce(sum(`l`.`output_tokens`),0) AS `total_output_tokens`, coalesce(sum((`l`.`input_tokens` + `l`.`output_tokens`)),0) AS `total_tokens`, coalesce(sum(`l`.`cost_usd`),0) AS `total_cost_usd`, coalesce(sum(`l`.`cost_brl`),0) AS `total_cost_brl`, coalesce(sum(`l`.`charged_brl`),0) AS `total_charged_brl`, coalesce(sum(`l`.`profit_brl`),0) AS `total_profit_brl`, coalesce(count(`l`.`id`),0) AS `requests_count`, coalesce(sum((case when (`l`.`success` = 0) then 1 else 0 end)),0) AS `error_count`, max(`l`.`created_at`) AS `last_request_at` FROM (`clients` `c` left join `ai_usage_logs` `l` on((`c`.`id` = `l`.`client_id`))) WHERE (`c`.`ai_enabled` = 1) GROUP BY `c`.`id`, `c`.`name`, `c`.`slug`, `c`.`ai_balance`, `c`.`ai_tokens_used`, `c`.`ai_requests_count` ;

-- --------------------------------------------------------

--
-- Estrutura para view `v_ai_saas_totals`
--
DROP TABLE IF EXISTS `v_ai_saas_totals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ai_saas_totals`  AS SELECT count(distinct `ai_usage_logs`.`client_id`) AS `clients_with_ai`, count(0) AS `total_requests`, sum((`ai_usage_logs`.`input_tokens` + `ai_usage_logs`.`output_tokens`)) AS `total_tokens`, sum(`ai_usage_logs`.`cost_usd`) AS `total_cost_usd`, sum(`ai_usage_logs`.`cost_brl`) AS `total_cost_brl`, sum(`ai_usage_logs`.`charged_brl`) AS `total_charged_brl`, sum(`ai_usage_logs`.`profit_brl`) AS `total_profit_brl`, round(((sum(`ai_usage_logs`.`profit_brl`) / nullif(sum(`ai_usage_logs`.`cost_brl`),0)) * 100),2) AS `profit_margin_percent`, sum((case when (`ai_usage_logs`.`success` = 0) then 1 else 0 end)) AS `total_errors` FROM `ai_usage_logs` WHERE (`ai_usage_logs`.`created_at` >= date_format(now(),'%Y-%m-01')) ;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ai_exceptions`
--
ALTER TABLE `ai_exceptions`
  ADD CONSTRAINT `fk_ai_exceptions_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `ai_usage_logs`
--
ALTER TABLE `ai_usage_logs`
  ADD CONSTRAINT `fk_ai_usage_logs_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `allergens`
--
ALTER TABLE `allergens`
  ADD CONSTRAINT `fk_allergens_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD CONSTRAINT `fk_analytics_events_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_analytics_events_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_analytics_events_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_analytics_events_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `api_integrations`
--
ALTER TABLE `api_integrations`
  ADD CONSTRAINT `fk_api_integrations_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `fk_tokens_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_target_customer_id_foreign` FOREIGN KEY (`target_customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_coupons_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `fk_cu_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cu_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cu_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_customers_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `customer_cashback`
--
ALTER TABLE `customer_cashback`
  ADD CONSTRAINT `fk_cashback_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cashback_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `customer_debts`
--
ALTER TABLE `customer_debts`
  ADD CONSTRAINT `fk_debts_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `fk_debts_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Restrições para tabelas `customer_debt_adjustments`
--
ALTER TABLE `customer_debt_adjustments`
  ADD CONSTRAINT `fk_debt_adjustments_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `customer_tags`
--
ALTER TABLE `customer_tags`
  ADD CONSTRAINT `fk_customer_tags_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `customer_tag_pivot`
--
ALTER TABLE `customer_tag_pivot`
  ADD CONSTRAINT `customer_tag_pivot_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_tag_pivot_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `customer_tags` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `delivery_distance_pricing`
--
ALTER TABLE `delivery_distance_pricing`
  ADD CONSTRAINT `fk_delivery_distance_pricing_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `delivery_fees`
--
ALTER TABLE `delivery_fees`
  ADD CONSTRAINT `fk_delivery_fees_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `delivery_rules`
--
ALTER TABLE `delivery_rules`
  ADD CONSTRAINT `fk_delivery_rules_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `delivery_schedules`
--
ALTER TABLE `delivery_schedules`
  ADD CONSTRAINT `fk_delivery_schedules_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD CONSTRAINT `fk_delivery_tracking_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delivery_tracking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `deployment_logs`
--
ALTER TABLE `deployment_logs`
  ADD CONSTRAINT `deployment_logs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deployment_logs_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD CONSTRAINT `fk_financial_transactions_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `fk_ingredients_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ingredients_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `instances`
--
ALTER TABLE `instances`
  ADD CONSTRAINT `fk_instances_clients` FOREIGN KEY (`assigned_to`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `loyalty_programs`
--
ALTER TABLE `loyalty_programs`
  ADD CONSTRAINT `fk_loyalty_programs_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD CONSTRAINT `loyalty_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loyalty_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `marketing_campaigns`
--
ALTER TABLE `marketing_campaigns`
  ADD CONSTRAINT `fk_marketing_campaigns_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `marketing_campaign_logs`
--
ALTER TABLE `marketing_campaign_logs`
  ADD CONSTRAINT `fk_campaign_logs_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `marketing_campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_campaign_logs_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `order_delivery_fees`
--
ALTER TABLE `order_delivery_fees`
  ADD CONSTRAINT `order_delivery_fees_delivery_fee_id_foreign` FOREIGN KEY (`delivery_fee_id`) REFERENCES `delivery_fees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_delivery_fees_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `order_ratings`
--
ALTER TABLE `order_ratings`
  ADD CONSTRAINT `order_ratings_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `order_statuses`
--
ALTER TABLE `order_statuses`
  ADD CONSTRAINT `fk_order_statuses_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_os_tpl` FOREIGN KEY (`whatsapp_template_id`) REFERENCES `whatsapp_templates` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `payment_settings`
--
ALTER TABLE `payment_settings`
  ADD CONSTRAINT `fk_payment_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `production_lists`
--
ALTER TABLE `production_lists`
  ADD CONSTRAINT `fk_production_lists_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `production_list_items`
--
ALTER TABLE `production_list_items`
  ADD CONSTRAINT `fk_production_list_items_list` FOREIGN KEY (`production_list_id`) REFERENCES `production_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_production_list_items_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `production_records`
--
ALTER TABLE `production_records`
  ADD CONSTRAINT `fk_production_records_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_production_records_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `product_allergen`
--
ALTER TABLE `product_allergen`
  ADD CONSTRAINT `product_allergen_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_allergen_ibfk_2` FOREIGN KEY (`allergen_id`) REFERENCES `allergens` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `product_ingredient`
--
ALTER TABLE `product_ingredient`
  ADD CONSTRAINT `fk_pi_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `product_wholesale_prices`
--
ALTER TABLE `product_wholesale_prices`
  ADD CONSTRAINT `product_wholesale_prices_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_wholesale_prices_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `fk_recipes_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recipes_packaging` FOREIGN KEY (`packaging_id`) REFERENCES `packagings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_recipes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recipes_variant_id` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `fk_recipe_ingredients_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recipe_ingredients_step` FOREIGN KEY (`recipe_step_id`) REFERENCES `recipe_steps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `recipe_steps`
--
ALTER TABLE `recipe_steps`
  ADD CONSTRAINT `fk_recipe_steps_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_referred_id_foreign` FOREIGN KEY (`referred_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_referrer_id_foreign` FOREIGN KEY (`referrer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `fk_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_plan_id_fk` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT;

--
-- Restrições para tabelas `subscription_addons`
--
ALTER TABLE `subscription_addons`
  ADD CONSTRAINT `subscription_addons_subscription_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `subscription_invoices`
--
ALTER TABLE `subscription_invoices`
  ADD CONSTRAINT `subscription_invoices_subscription_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `subscription_notifications`
--
ALTER TABLE `subscription_notifications`
  ADD CONSTRAINT `subscription_notifications_subscription_id_fk` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `whatsapp_campaigns`
--
ALTER TABLE `whatsapp_campaigns`
  ADD CONSTRAINT `fk_campaign_test_customer` FOREIGN KEY (`test_customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_whatsapp_campaigns_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `whatsapp_campaign_logs`
--
ALTER TABLE `whatsapp_campaign_logs`
  ADD CONSTRAINT `whatsapp_campaign_logs_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `whatsapp_campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `whatsapp_campaign_logs_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `whatsapp_instances`
--
ALTER TABLE `whatsapp_instances`
  ADD CONSTRAINT `fk_whatsapp_instances_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `whatsapp_instance_urls`
--
ALTER TABLE `whatsapp_instance_urls`
  ADD CONSTRAINT `whatsapp_instance_urls_client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  ADD CONSTRAINT `fk_whatsapp_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `whatsapp_templates`
--
ALTER TABLE `whatsapp_templates`
  ADD CONSTRAINT `fk_whatsapp_templates_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
