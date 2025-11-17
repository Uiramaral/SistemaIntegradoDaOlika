-- SQL para verificar se há variant_id nos pedidos e se os produtos têm peso
-- Execute este SQL para diagnosticar o problema

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

-- 4. Verificar alguns itens de pedido recentes com seus dados
SELECT 
    oi.id,
    oi.order_id,
    oi.product_id,
    oi.variant_id,
    oi.custom_name,
    p.name as product_name,
    p.weight_grams as product_weight,
    pv.name as variant_name,
    pv.weight_grams as variant_weight
FROM order_items oi
LEFT JOIN products p ON oi.product_id = p.id
LEFT JOIN product_variants pv ON oi.variant_id = pv.id
ORDER BY oi.id DESC
LIMIT 10;

