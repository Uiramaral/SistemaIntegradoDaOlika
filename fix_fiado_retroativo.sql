-- ====================================================================
-- CORREÇÃO RETROATIVA: Fiados quitados sem transação financeira
-- ====================================================================
-- Este script corrige pedidos "fiado" que foram quitados mas não 
-- entraram nas estatísticas financeiras (sem transação de receita)
-- ====================================================================

-- =====================================================================
-- PASSO 0: CORRIGIR DESCRIÇÕES JÁ EXISTENTES
-- =====================================================================
-- Remove o texto "(retroativo)" e "- Fiado quitado" das descrições
UPDATE financial_transactions 
SET description = REPLACE(REPLACE(description, ' - Fiado quitado (retroativo)', ''), ' - Pagamento de fiado', '')
WHERE description LIKE '%retroativo%' 
   OR description LIKE '%Pagamento de fiado%';

SELECT 
    cd.id AS debt_id,
    cd.order_id,
    cd.amount AS debt_amount,
    cd.status AS debt_status,
    cd.updated_at AS debt_settled_at,
    o.order_number,
    o.payment_status,
    COALESCE(o.final_amount, o.total_amount) AS order_amount,
    ft.id AS transaction_id
FROM customer_debts cd
JOIN orders o ON o.id = cd.order_id
LEFT JOIN financial_transactions ft ON ft.order_id = o.id AND ft.type = 'revenue'
WHERE cd.type = 'debit'
  AND cd.status = 'settled'
  AND ft.id IS NULL
ORDER BY cd.id DESC;

-- =====================================================================
-- PASSO 2: ATUALIZAR payment_status dos pedidos para 'paid'
-- =====================================================================
-- Isso garante que o pedido seja considerado "pago" nas estatísticas
UPDATE orders o
INNER JOIN customer_debts cd ON cd.order_id = o.id
SET o.payment_status = 'paid'
WHERE cd.type = 'debit'
  AND cd.status = 'settled'
  AND (o.payment_status IS NULL OR o.payment_status != 'paid');

-- =====================================================================
-- PASSO 3: CRIAR transações financeiras de receita
-- =====================================================================
-- Isso registra a receita na data em que o débito foi quitado
INSERT INTO financial_transactions (
    client_id, 
    type, 
    amount, 
    description, 
    transaction_date, 
    category, 
    order_id, 
    created_at, 
    updated_at
)
SELECT DISTINCT
    o.client_id,
    'revenue' AS type,
    COALESCE(o.final_amount, o.total_amount) AS amount,
    CONCAT('Pedido ', COALESCE(o.order_number, CONCAT('#', o.id))) AS description,
    DATE(cd.updated_at) AS transaction_date,
    'Pedidos' AS category,
    o.id AS order_id,
    NOW() AS created_at,
    NOW() AS updated_at
FROM customer_debts cd
JOIN orders o ON o.id = cd.order_id
LEFT JOIN financial_transactions ft ON ft.order_id = o.id AND ft.type = 'revenue'
WHERE cd.type = 'debit'
  AND cd.status = 'settled'
  AND ft.id IS NULL
  AND o.client_id IS NOT NULL
  AND COALESCE(o.final_amount, o.total_amount) > 0;

-- =====================================================================
-- PASSO 4: VERIFICAÇÃO - Conferir se tudo foi corrigido
-- =====================================================================
SELECT 
    COUNT(*) AS debitos_sem_transacao
FROM customer_debts cd
JOIN orders o ON o.id = cd.order_id
LEFT JOIN financial_transactions ft ON ft.order_id = o.id AND ft.type = 'revenue'
WHERE cd.type = 'debit'
  AND cd.status = 'settled'
  AND ft.id IS NULL;
-- Resultado esperado: 0
