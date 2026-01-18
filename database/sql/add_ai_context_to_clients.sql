-- =====================================================
-- ADICIONAR CONTEXTO DE IA PARA MULTI-TENANCY
-- =====================================================
-- Cada estabelecimento terá seu próprio contexto personalizado
-- para interações com IA (Gemini)

ALTER TABLE `clientes` 
ADD COLUMN `ai_context` TEXT NULL COMMENT 'Contexto/instruções específicas para IA (cardápio, horários, regras de negócio)' AFTER `whatsapp_phone`,
ADD COLUMN `ai_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Se o cliente tem IA habilitada' AFTER `ai_context`,
ADD COLUMN `ai_safety_level` ENUM('none', 'low', 'medium', 'high') NOT NULL DEFAULT 'medium' COMMENT 'Nível de segurança para prevenir prompt injection' AFTER `ai_enabled`;

-- Comentário na tabela
ALTER TABLE `clientes` 
COMMENT = 'Estabelecimentos (tenants) do SaaS - cada um com seu contexto de IA isolado';
