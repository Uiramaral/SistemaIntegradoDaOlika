-- Script de diagnóstico completo do banco de dados
-- Verifica estrutura e corrige problemas encontrados

-- 1. Verificar estrutura da tabela users
SELECT '=== TABELA USERS ===' as info;
DESCRIBE users;

-- 2. Verificar se existe usuário admin
SELECT '=== USUÁRIO ADMIN ===' as info;
SELECT id, name, email, 
    CASE 
        WHEN password LIKE '$2y$%' THEN 'bcrypt (correto)'
        WHEN password LIKE '$2a$%' THEN 'bcrypt (versão antiga)'
        WHEN LENGTH(password) = 32 THEN 'MD5 (incorreto)'
        WHEN LENGTH(password) = 40 THEN 'SHA1 (incorreto)'
        ELSE 'Formato desconhecido'
    END as formato_senha
FROM users WHERE email = 'admin@olika.com';

-- 3. Verificar estrutura da tabela orders
SELECT '=== TABELA ORDERS ===' as info;
DESCRIBE orders;

-- 4. Verificar estrutura da tabela customers
SELECT '=== TABELA CUSTOMERS ===' as info;
DESCRIBE customers;

-- 5. Verificar estrutura da tabela products
SELECT '=== TABELA PRODUCTS ===' as info;
DESCRIBE products;

-- 6. Verificar estrutura da tabela categories
SELECT '=== TABELA CATEGORIES ===' as info;
DESCRIBE categories;

-- 7. Verificar estrutura da tabela coupons
SELECT '=== TABELA COUPONS ===' as info;
DESCRIBE coupons;

-- 8. Contar registros em cada tabela
SELECT '=== CONTAGEM DE REGISTROS ===' as info;
SELECT 'users' as tabela, COUNT(*) as total FROM users
UNION ALL
SELECT 'orders', COUNT(*) FROM orders
UNION ALL
SELECT 'customers', COUNT(*) FROM customers
UNION ALL
SELECT 'products', COUNT(*) FROM products
UNION ALL
SELECT 'categories', COUNT(*) FROM categories
UNION ALL
SELECT 'coupons', COUNT(*) FROM coupons;
