-- Atualizar client_id dos ingredientes existentes
-- Data: 2026-01-25
-- Este SQL atualiza todos os ingredientes que não têm client_id
-- com o client_id padrão do sistema (ou o primeiro cliente disponível)

-- Opção 1: Atualizar com o client_id padrão da configuração
-- Descomente e ajuste o ID do cliente conforme necessário
-- UPDATE `ingredients` SET `client_id` = 1 WHERE `client_id` IS NULL;

-- Opção 2: Atualizar com o primeiro cliente disponível
UPDATE `ingredients` 
SET `client_id` = (
    SELECT `id` FROM `clients` ORDER BY `id` LIMIT 1
)
WHERE `client_id` IS NULL;

-- Verificar resultado
SELECT COUNT(*) as total_ingredients,
       COUNT(CASE WHEN client_id IS NULL THEN 1 END) as sem_client_id,
       COUNT(CASE WHEN client_id IS NOT NULL THEN 1 END) as com_client_id
FROM `ingredients`;
