-- Atualizar o campo total_debts para todos os clientes existentes
-- baseado nos débitos abertos (status='open') na tabela customer_debts
-- NOTA: Execute este script APÓS executar add_total_debts_to_customers.sql

UPDATE `customers` c
SET `total_debts` = COALESCE((
    SELECT 
        SUM(CASE WHEN cd.type = 'debit' THEN cd.amount ELSE 0 END) - 
        SUM(CASE WHEN cd.type = 'credit' THEN cd.amount ELSE 0 END)
    FROM `customer_debts` cd
    WHERE cd.customer_id = c.id 
    AND cd.status = 'open'
), 0);

