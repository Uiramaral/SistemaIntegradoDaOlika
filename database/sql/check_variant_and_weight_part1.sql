-- SQL Parte 1: Verificações de contagem
-- Execute este SQL primeiro

-- 1. Verificar se há pedidos com variant_id
SELECT 
    COUNT(*) as total_items,
    COUNT(variant_id) as items_with_variant,
    COUNT(*) - COUNT(variant_id) as items_without_variant
FROM order_items;

-- 2. Verificar produtos com peso cadastrado
SELECT 
    COUNT(*) as total_products,
    COUNT(weight_grams) as products_with_weight,
    COUNT(*) - COUNT(weight_grams) as products_without_weight
FROM products;

-- 3. Verificar variantes com peso cadastrado
SELECT 
    COUNT(*) as total_variants,
    COUNT(weight_grams) as variants_with_weight,
    COUNT(*) - COUNT(weight_grams) as variants_without_weight
FROM product_variants;

