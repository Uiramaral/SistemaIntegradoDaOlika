-- Adicionar campo packaging_id na tabela recipes
ALTER TABLE `recipes` 
ADD COLUMN `packaging_id` BIGINT UNSIGNED NULL AFTER `packaging_cost`,
ADD CONSTRAINT `fk_recipes_packaging` FOREIGN KEY (`packaging_id`) REFERENCES `packagings` (`id`) ON DELETE SET NULL;
