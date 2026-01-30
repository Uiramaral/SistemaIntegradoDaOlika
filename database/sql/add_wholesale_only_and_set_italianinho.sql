-- Produtos exclusivos de revenda: wholesale_only = 1 OU show_in_catalog = 0.
-- Só aparecem para clientes is_wholesale = 1. Demais produtos aparecem para todos,
-- com preço normal (cliente final) ou revenda (product_wholesale_prices) conforme o cliente.

-- Marcar Italianinho (e nomes que contenham) como exclusivos de revenda.
UPDATE products
SET wholesale_only = 1
WHERE (name LIKE 'Italianinho%' OR name LIKE '%Italianinho%')
  AND is_active = 1;
