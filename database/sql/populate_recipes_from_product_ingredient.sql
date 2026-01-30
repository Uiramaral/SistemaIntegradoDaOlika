-- Popular receitas a partir de product_ingredient
-- Para cada produto com ingredientes em product_ingredient que ainda não tem receita:
--   cria recipe (product_id, name...), recipe_steps (Etapa 1), recipe_ingredients (de product_ingredient)
-- Executar depois de add_product_id_to_recipes.sql
-- Data: 2026-01-25

DELIMITER $$

DROP PROCEDURE IF EXISTS populate_recipes_from_pi$$

CREATE PROCEDURE populate_recipes_from_pi()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_product_id BIGINT UNSIGNED;
    DECLARE v_client_id BIGINT UNSIGNED;
    DECLARE v_name VARCHAR(255);
    DECLARE v_category VARCHAR(100);
    DECLARE v_recipe_id BIGINT UNSIGNED;
    DECLARE v_step_id BIGINT UNSIGNED;

    DECLARE cur CURSOR FOR
        SELECT p.id, p.client_id, p.name, COALESCE(c.name, 'Geral')
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        INNER JOIN (SELECT DISTINCT product_id FROM product_ingredient) pi ON p.id = pi.product_id
        LEFT JOIN recipes r ON r.product_id = p.id
        WHERE r.id IS NULL;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_product_id, v_client_id, v_name, v_category;
        IF done THEN
            LEAVE read_loop;
        END IF;

        INSERT INTO recipes (
            client_id, product_id, name, category,
            total_weight, hydration, levain, is_active,
            use_milk_instead_of_water, is_fermented, is_bread, include_notes_in_print,
            packaging_cost, cost, created_at, updated_at
        ) VALUES (
            v_client_id, v_product_id, v_name, v_category,
            700, 70, 30, 1,
            0, 1, 1, 0,
            0.5, 0, NOW(), NOW()
        );

        SET v_recipe_id = LAST_INSERT_ID();

        INSERT INTO recipe_steps (recipe_id, name, sort_order, created_at, updated_at)
        VALUES (v_recipe_id, 'Etapa 1', 0, NOW(), NOW());

        SET v_step_id = LAST_INSERT_ID();

        INSERT INTO recipe_ingredients (recipe_step_id, ingredient_id, type, percentage, sort_order, created_at, updated_at)
        SELECT
            v_step_id,
            pi.ingredient_id,
            'ingredient',
            COALESCE(pi.percentage, 0),
            (SELECT COUNT(*) FROM product_ingredient pi2
             INNER JOIN ingredients i2 ON pi2.ingredient_id = i2.id
             AND LOWER(TRIM(i2.name)) != LOWER(TRIM(v_name))
             WHERE pi2.product_id = v_product_id AND pi2.ingredient_id <= pi.ingredient_id) - 1,
            NOW(),
            NOW()
        FROM product_ingredient pi
        INNER JOIN ingredients i ON pi.ingredient_id = i.id
            AND LOWER(TRIM(i.name)) != LOWER(TRIM(v_name))
        WHERE pi.product_id = v_product_id
        ORDER BY pi.ingredient_id;

    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

CALL populate_recipes_from_pi();
DROP PROCEDURE IF EXISTS populate_recipes_from_pi;

-- Resumo
SELECT 'Receitas criadas a partir de product_ingredient' AS info;
SELECT COUNT(*) AS total_recipes FROM recipes WHERE product_id IS NOT NULL;
