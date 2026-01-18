-- =====================================================
-- LIMPAR E RECRIAR INTEGRAÇÕES CORRETAMENTE
-- =====================================================
-- Este SQL corrige o problema de credentials salvas 
-- com estrutura de campos ao invés de valores

-- Deletar todas as integrações existentes (dados incorretos)
DELETE FROM `api_integrations` WHERE `client_id` = 1;

-- As novas integrações serão criadas automaticamente 
-- pela aplicação com a estrutura correta quando acessar
-- a página /dashboard/integrations
