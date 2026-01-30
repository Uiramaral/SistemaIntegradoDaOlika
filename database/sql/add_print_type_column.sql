-- Adicionar coluna print_type à tabela orders
-- Execução: Copiar e colar no phpMyAdmin do servidor

ALTER TABLE `orders` 
ADD COLUMN `print_type` VARCHAR(20) NULL DEFAULT 'normal' 
COMMENT 'Tipo de recibo: normal (com preços) ou check (sem preços)' 
AFTER `printed_at`;

-- Verificar se foi criada
SELECT COUNT(*) as total_orders, 
       SUM(CASE WHEN print_type IS NULL THEN 1 ELSE 0 END) as null_print_type,
       SUM(CASE WHEN print_type = 'normal' THEN 1 ELSE 0 END) as normal_print_type,
       SUM(CASE WHEN print_type = 'check' THEN 1 ELSE 0 END) as check_print_type
FROM orders;
