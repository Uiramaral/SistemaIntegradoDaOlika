-- =====================================================================
-- FIX CONEXÃO WHATSAPP - 2026-01-13
-- =====================================================================
-- Este SQL verifica e corrige configurações da integração WhatsApp
-- =====================================================================

-- =====================================================================
-- PASSO 1: Verificar configuração atual do WhatsApp
-- =====================================================================
SELECT 
    id,
    instance_name,
    api_url,
    api_key,
    active,
    CASE 
        WHEN api_url LIKE '%railway.app%' THEN '⚠️ RAILWAY - Pode estar fora do ar'
        WHEN api_url LIKE '%localhost%' THEN '⚠️ LOCALHOST - Não funciona em produção'
        WHEN api_url IS NULL OR api_url = '' THEN '❌ NÃO CONFIGURADO'
        ELSE '✓ Configurado'
    END as status_url
FROM whatsapp_settings
WHERE active = 1;

-- =====================================================================
-- PASSO 2: Se a URL do Railway estiver dando timeout, você precisa:
-- 
-- OPÇÃO A: Verificar se o serviço Railway está rodando
--          Acesse: https://railway.app/ e verifique o deploy
--
-- OPÇÃO B: Usar Evolution API auto-hospedada
--          Configure uma nova URL da API Evolution
--
-- OPÇÃO C: Atualizar para nova URL (execute o UPDATE abaixo)
-- =====================================================================

-- DESCOMENTE E ATUALIZE a linha abaixo para mudar a URL da API
-- UPDATE whatsapp_settings 
-- SET api_url = 'https://SUA_NOVA_URL_AQUI.com'
-- WHERE active = 1;

-- =====================================================================
-- PASSO 3: Verificar se os campos necessários existem
-- =====================================================================
-- Se der erro "Unknown column", execute:
-- ALTER TABLE whatsapp_settings ADD COLUMN created_at TIMESTAMP NULL DEFAULT NULL;
-- ALTER TABLE whatsapp_settings ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL;

-- =====================================================================
-- DIAGNÓSTICO: Verificar logs de erro
-- =====================================================================
-- Os erros de timeout indicam que a API em:
-- https://integracao-whatsapp-01.up.railway.app/
-- NÃO ESTÁ RESPONDENDO
--
-- Possíveis causas:
-- 1. Serviço Railway pausado por inatividade (free tier)
-- 2. Deploy com erro
-- 3. Problema de rede
-- 4. API key incorreta
-- =====================================================================

-- =====================================================================
-- RESUMO DAS CORREÇÕES NO CÓDIGO:
-- =====================================================================
-- 1. Adicionada meta tag CSRF no layout admin.blade.php
-- 2. Criado método whatsappConnect() no SettingsController
-- 3. Adicionada rota POST /settings/whatsapp/connect
-- 4. Melhorado tratamento de erros no JavaScript
-- 5. Adicionado timeout no fetch para evitar travamento
-- =====================================================================
