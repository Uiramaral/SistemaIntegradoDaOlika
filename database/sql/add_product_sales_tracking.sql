-- SQL para rastrear vendas de produtos e permitir ordenação por mais vendidos
-- Este script adiciona índices e cria uma view/materializada para produtos mais vendidos

-- 1. Adicionar índices para melhorar performance das consultas de vendas (se não existirem)
-- Verificar e criar índices apenas se não existirem
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order_items' 
    AND INDEX_NAME = 'idx_order_items_product_id'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_order_items_product_id ON order_items(product_id)',
    'SELECT "Índice idx_order_items_product_id já existe" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'order_items' 
    AND INDEX_NAME = 'idx_order_items_order_id'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_order_items_order_id ON order_items(order_id)',
    'SELECT "Índice idx_order_items_order_id já existe" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND INDEX_NAME = 'idx_orders_payment_status'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_orders_payment_status ON orders(payment_status)',
    'SELECT "Índice idx_orders_payment_status já existe" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'orders' 
    AND INDEX_NAME = 'idx_orders_created_at'
);

SET @sql = IF(@index_exists = 0,
    'CREATE INDEX idx_orders_created_at ON orders(created_at)',
    'SELECT "Índice idx_orders_created_at já existe" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Criar view para produtos mais vendidos (últimos 90 dias)
-- Esta view calcula a quantidade vendida de cada produto nos últimos 90 dias
-- Nota: payment_status usa 'paid' (não 'approved' no banco atual)
DROP VIEW IF EXISTS product_sales_last_90_days;

CREATE VIEW product_sales_last_90_days AS
SELECT 
    oi.product_id,
    p.name as product_name,
    COALESCE(SUM(oi.quantity), 0) as total_quantity_sold,
    COUNT(DISTINCT oi.order_id) as total_orders,
    COALESCE(SUM(oi.total_price), 0) as total_revenue
FROM order_items oi
INNER JOIN orders o ON oi.order_id = o.id
INNER JOIN products p ON oi.product_id = p.id
WHERE o.payment_status = 'paid'
  AND o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY oi.product_id, p.name
ORDER BY total_quantity_sold DESC;

