-- SQL script to add variant_id to recipes table
ALTER TABLE recipes ADD COLUMN variant_id bigint unsigned NULL AFTER product_id;
ALTER TABLE recipes ADD CONSTRAINT fk_recipes_variant_id FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL;
