-- Verificar registros de cashback no banco de dados
-- Execute este SQL para ver todos os registros de cashback

-- Ver todos os registros de cashback
SELECT 
    cc.id,
    cc.customer_id,
    c.name AS customer_name,
    cc.order_id,
    cc.amount,
    cc.type,
    cc.description,
    cc.created_at
FROM customer_cashback cc
LEFT JOIN customers c ON c.id = cc.customer_id
ORDER BY cc.created_at DESC;

-- Calcular saldo por cliente
SELECT 
    c.id AS customer_id,
    c.name AS customer_name,
    c.phone,
    c.email,
    COALESCE(SUM(CASE WHEN cc.type = 'credit' THEN cc.amount ELSE 0 END), 0) AS total_credits,
    COALESCE(SUM(CASE WHEN cc.type = 'debit' THEN cc.amount ELSE 0 END), 0) AS total_debits,
    COALESCE(SUM(CASE WHEN cc.type = 'credit' THEN cc.amount ELSE 0 END), 0) - 
    COALESCE(SUM(CASE WHEN cc.type = 'debit' THEN cc.amount ELSE 0 END), 0) AS balance
FROM customers c
LEFT JOIN customer_cashback cc ON cc.customer_id = c.id
GROUP BY c.id, c.name, c.phone, c.email
HAVING balance > 0
ORDER BY balance DESC;

-- Verificar se a tabela existe e tem estrutura correta
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'customer_cashback'
ORDER BY ORDINAL_POSITION;

