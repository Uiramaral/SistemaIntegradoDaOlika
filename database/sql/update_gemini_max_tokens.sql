-- ========================================
-- ATUALIZAR MAX_TOKENS DO GEMINI PARA 5000
-- Data: 2026-01-25
-- Descrição: Aumenta o limite de tokens de saída do Gemini para 5000
--            para permitir respostas completas e detalhadas no Assistente IA
-- ========================================

-- Atualizar TODOS os registros do Gemini para ter max_tokens = 5000
UPDATE `api_integrations`
SET `settings` = JSON_SET(
    COALESCE(`settings`, '{}'),
    '$.max_tokens', 5000
)
WHERE `provider` = 'gemini';

-- Verificar resultado
SELECT 
    id,
    provider,
    client_id,
    JSON_EXTRACT(settings, '$.max_tokens') as max_tokens,
    JSON_EXTRACT(settings, '$.model') as model
FROM `api_integrations`
WHERE `provider` = 'gemini';
