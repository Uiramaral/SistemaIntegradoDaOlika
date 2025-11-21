-- Adicionar campo newsletter na tabela customers
-- Execute este script no seu banco de dados

ALTER TABLE customers 
ADD COLUMN newsletter BOOLEAN DEFAULT 0 NOT NULL AFTER is_active;

-- Adicionar índice para consultas mais rápidas
ALTER TABLE customers 
ADD INDEX idx_newsletter (newsletter);

-- Verificar se foi adicionado
DESCRIBE customers;

