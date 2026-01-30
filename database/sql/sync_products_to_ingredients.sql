-- Sincronizar produtos (products) com ingredientes (ingredients)
-- Cria ingredientes baseados em produtos existentes que ainda não têm correspondente
-- Data: 2026-01-25

-- ============================================================================
-- PARTE 1: Criar ingredientes a partir de produtos que ainda não existem
-- ============================================================================

-- Inserir ingredientes baseados em produtos
-- Apenas produtos que não têm ingrediente correspondente (mesmo nome)
-- Usa ID do produto no slug para garantir unicidade
INSERT INTO `ingredients` (
    `client_id`,
    `name`,
    `slug`,
    `category`,
    `package_weight`,
    `cost`,
    `unit`,
    `is_active`,
    `created_at`,
    `updated_at`
)
SELECT DISTINCT
    p.`client_id`,
    p.`name` AS `name`,
    -- Gerar slug único usando ID do produto para garantir unicidade
    -- Formato: produto-{id}
    CONCAT('produto-', p.`id`) AS `slug`,
    COALESCE(c.`name`, 'Geral') AS `category`,
    -- Usar weight_grams do produto como package_weight (se disponível)
    CASE 
        WHEN p.`weight_grams` IS NOT NULL AND p.`weight_grams` > 0 
        THEN p.`weight_grams` 
        ELSE NULL 
    END AS `package_weight`,
    -- Tentar calcular custo baseado no preço (se houver lógica de margem)
    -- Por padrão, usar 30% do preço como custo estimado
    CASE 
        WHEN p.`price` > 0 
        THEN ROUND(p.`price` * 0.30, 2)
        ELSE 0 
    END AS `cost`,
    'g' AS `unit`,
    1 AS `is_active`,
    NOW() AS `created_at`,
    NOW() AS `updated_at`
FROM `products` p
LEFT JOIN `categories` c ON p.`category_id` = c.`id`
LEFT JOIN `ingredients` i ON LOWER(TRIM(p.`name`)) = LOWER(TRIM(i.`name`)) 
    AND (p.`client_id` = i.`client_id` OR (p.`client_id` IS NULL AND i.`client_id` IS NULL))
WHERE 
    p.`is_active` = 1
    AND i.`id` IS NULL  -- Apenas produtos sem ingrediente correspondente
    AND p.`name` IS NOT NULL 
    AND TRIM(p.`name`) != '';

-- ============================================================================
-- PARTE 2: Atualizar ingredientes existentes com dados dos produtos
-- (se o produto tiver informações mais recentes)
-- ============================================================================

-- Atualizar package_weight se o produto tiver weight_grams e o ingrediente não tiver
UPDATE `ingredients` i
INNER JOIN `products` p ON LOWER(TRIM(i.`name`)) = LOWER(TRIM(p.`name`))
    AND (i.`client_id` = p.`client_id` OR (i.`client_id` IS NULL AND p.`client_id` IS NULL))
SET 
    i.`package_weight` = CASE 
        WHEN (i.`package_weight` IS NULL OR i.`package_weight` = 0) 
             AND p.`weight_grams` IS NOT NULL 
             AND p.`weight_grams` > 0
        THEN p.`weight_grams`
        ELSE i.`package_weight`
    END,
    i.`updated_at` = NOW()
WHERE 
    p.`is_active` = 1
    AND i.`is_active` = 1;

-- ============================================================================
-- PARTE 3: Criar relação entre produtos e ingredientes (tabela pivot)
-- ============================================================================

-- Verificar se a tabela product_ingredient existe, se não, criar
-- Nota: A tabela existente não tem id, created_at, updated_at
CREATE TABLE IF NOT EXISTS `product_ingredient` (
    `product_id` BIGINT UNSIGNED NOT NULL,
    `ingredient_id` INT UNSIGNED NOT NULL,
    `percentage` DECIMAL(8,2) NULL COMMENT 'Porcentagem do ingrediente no produto',
    PRIMARY KEY (`product_id`, `ingredient_id`),
    KEY `idx_product_ingredient_product` (`product_id`),
    KEY `idx_product_ingredient_ingredient` (`ingredient_id`),
    CONSTRAINT `fk_product_ingredient_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_product_ingredient_ingredient` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar relações automáticas entre produtos e ingredientes com mesmo nome
-- A tabela existente não tem created_at e updated_at
INSERT INTO `product_ingredient` (`product_id`, `ingredient_id`)
SELECT DISTINCT
    p.`id` AS `product_id`,
    i.`id` AS `ingredient_id`
FROM `products` p
INNER JOIN `ingredients` i ON LOWER(TRIM(p.`name`)) = LOWER(TRIM(i.`name`))
    AND (p.`client_id` = i.`client_id` OR (p.`client_id` IS NULL AND i.`client_id` IS NULL))
LEFT JOIN `product_ingredient` pi ON p.`id` = pi.`product_id` AND i.`id` = pi.`ingredient_id`
WHERE 
    p.`is_active` = 1
    AND i.`is_active` = 1
    AND pi.`product_id` IS NULL;  -- Apenas relações que ainda não existem

-- ============================================================================
-- RESUMO: Mostrar estatísticas
-- ============================================================================

SELECT 
    'Produtos ativos' AS `tipo`,
    COUNT(*) AS `total`
FROM `products`
WHERE `is_active` = 1

UNION ALL

SELECT 
    'Ingredientes ativos' AS `tipo`,
    COUNT(*) AS `total`
FROM `ingredients`
WHERE `is_active` = 1

UNION ALL

SELECT 
    'Relações produto-ingrediente' AS `tipo`,
    COUNT(*) AS `total`
FROM `product_ingredient`;
