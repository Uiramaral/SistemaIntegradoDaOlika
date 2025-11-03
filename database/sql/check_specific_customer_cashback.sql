-- Verificar cashback de um cliente específico
-- Substitua CUSTOMER_ID pelo ID do cliente que está vendo o cashback errado

-- Ver todos os registros de cashback de um cliente
SELECT 
    cc.id,
    cc.customer_id,
    c.name AS customer_name,
    cc.order_id,
    o.order_number,
    cc.amount,
    cc.type,
    cc.description,
    cc.created_at
FROM customer_cashback cc
LEFT JOIN customers c ON c.id = cc.customer_id
LEFT JOIN orders o ON o.id = cc.order_id
WHERE cc.customer_id = CUSTOMER_ID  -- Substitua pelo ID do cliente
ORDER BY cc.created_at DESC;

-- Calcular saldo manualmente para verificar
SELECT 
    'Créditos' AS tipo,
    SUM(amount) AS total
FROM customer_cashback
WHERE customer_id = CUSTOMER_ID  -- Substitua pelo ID do cliente
  AND type = 'credit'
UNION ALL
SELECT 
    'Débitos' AS tipo,
    SUM(amount) AS total
FROM customer_cashback
WHERE customer_id = CUSTOMER_ID  -- Substitua pelo ID do cliente
  AND type = 'debit'
UNION ALL
SELECT 
    'Saldo Final' AS tipo,
    COALESCE(
        (SELECT SUM(amount) FROM customer_cashback WHERE customer_id = CUSTOMER_ID AND type = 'credit'), 0
    ) - COALESCE(
        (SELECT SUM(amount) FROM customer_cashback WHERE customer_id = CUSTOMER_ID AND type = 'debit'), 0
    ) AS total;

-- Verificar se há registros duplicados ou problemas
SELECT 
    customer_id,
    type,
    COUNT(*) AS count,
    SUM(amount) AS total_amount
FROM customer_cashback
WHERE customer_id = CUSTOMER_ID  -- Substitua pelo ID do cliente
GROUP BY customer_id, type;

