-- SQL Parte 2: Detalhes dos itens de pedido
-- Execute este SQL depois da parte 1

-- Verificar alguns itens de pedido recentes com seus dados
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

