-- ============================================
-- EVOLUTION API + IA - AJUSTES BANCO
-- ============================================
-- Execute este SQL para adicionar campos de IA e Evolution API

-- Ampliar whatsapp_settings para IA e status
ALTER TABLE whatsapp_settings
  ADD COLUMN ai_enabled TINYINT(1) DEFAULT 0,
  ADD COLUMN openai_api_key VARCHAR(255) NULL,
  ADD COLUMN openai_model VARCHAR(100) DEFAULT 'gpt-4o-mini',
  ADD COLUMN ai_system_prompt TEXT NULL,
  ADD COLUMN admin_phone VARCHAR(32) NULL;

-- Template de sistema padrão (persona do atendente)
UPDATE whatsapp_settings
SET ai_system_prompt = COALESCE(ai_system_prompt,
'Você é o atendente virtual da Olika Cozinha Artesanal. Responda com simpatia e objetividade.

Se o cliente pedir status do pedido, busque pelo número/telefone e informe de forma clara.
Se questionarem preços ou cardápio, consulte o BD. Se não houver dados, peça detalhes de forma breve.
Nunca forneça dados sensíveis. Sempre assine como "Olika".');

-- ============================================
-- FIM
-- ============================================
