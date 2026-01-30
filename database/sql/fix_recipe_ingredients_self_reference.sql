-- Corrigir lista de ingredientes: remover autorreferências
-- Remove recipe_ingredients onde o ingrediente tem o mesmo nome do produto da receita
-- (receita não pode ter a si mesma como ingrediente)
-- Data: 2026-01-25

-- 1. Remover de recipe_ingredients os que são autorreferência
DELETE ri FROM recipe_ingredients ri
INNER JOIN recipe_steps rs ON ri.recipe_step_id = rs.id
INNER JOIN recipes r ON rs.recipe_id = r.id
INNER JOIN products p ON r.product_id = p.id
INNER JOIN ingredients i ON ri.ingredient_id = i.id
WHERE LOWER(TRIM(p.name)) = LOWER(TRIM(i.name));

-- 2. Remover de product_ingredient os que são autorreferência
DELETE pi FROM product_ingredient pi
INNER JOIN products p ON pi.product_id = p.id
INNER JOIN ingredients i ON pi.ingredient_id = i.id
WHERE LOWER(TRIM(p.name)) = LOWER(TRIM(i.name));

-- Resumo
SELECT 'Autorreferências removidas' AS info;
SELECT COUNT(*) AS recipe_ingredients_restantes FROM recipe_ingredients;
SELECT COUNT(*) AS product_ingredient_restantes FROM product_ingredient;
