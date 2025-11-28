-- ============================================
-- ÍNDICES PARA OTIMIZAÇÃO DE PERFORMANCE
-- Sistema Unificado da Olika
-- ============================================
-- 
-- Execute este script no banco de dados para melhorar
-- significativamente a performance do dashboard.
--
-- IMPORTANTE: Faça backup antes de executar!
-- ============================================

-- ============================================
-- ÍNDICES PARA TABELA ORDERS
-- ============================================

-- Índice para busca por data de criação (usado em várias queries)
CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders(created_at);

-- Índice para busca por status (usado em filtros e contagens)
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);

-- Índice para busca por status de pagamento (usado em filtros e contagens)
CREATE INDEX IF NOT EXISTS idx_orders_payment_status ON orders(payment_status);

-- Índice composto para status e payment_status (otimiza queries combinadas)
CREATE INDEX IF NOT EXISTS idx_orders_status_payment ON orders(status, payment_status);

-- Índice para busca por data de entrega agendada
CREATE INDEX IF NOT EXISTS idx_orders_scheduled_delivery_at ON orders(scheduled_delivery_at);

-- Índice para busca por número do pedido (usado em buscas)
CREATE INDEX IF NOT EXISTS idx_orders_order_number ON orders(order_number);

-- Índice para foreign key customer_id (melhora joins)
CREATE INDEX IF NOT EXISTS idx_orders_customer_id ON orders(customer_id);

-- Índice para foreign key address_id (melhora joins)
CREATE INDEX IF NOT EXISTS idx_orders_address_id ON orders(address_id);

-- Índice para foreign key payment_id (melhora joins)
CREATE INDEX IF NOT EXISTS idx_orders_payment_id ON orders(payment_id);

-- Índice composto para queries de hoje (created_at + payment_status)
CREATE INDEX IF NOT EXISTS idx_orders_today_stats ON orders(created_at, payment_status);

-- ============================================
-- ÍNDICES PARA TABELA ORDER_ITEMS
-- ============================================

-- Índice para foreign key order_id (melhora joins e agregações)
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);

-- Índice para foreign key product_id (melhora joins e top produtos)
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);

-- Índice composto para agregações de produtos (product_id + order_id)
CREATE INDEX IF NOT EXISTS idx_order_items_product_order ON order_items(product_id, order_id);

-- ============================================
-- ÍNDICES PARA TABELA CUSTOMERS
-- ============================================

-- Índice para busca por data de criação (novos clientes)
CREATE INDEX IF NOT EXISTS idx_customers_created_at ON customers(created_at);

-- Índice para busca por nome (usado em buscas)
CREATE INDEX IF NOT EXISTS idx_customers_name ON customers(name);

-- Índice para busca por telefone (usado em buscas)
CREATE INDEX IF NOT EXISTS idx_customers_phone ON customers(phone);

-- Índice composto para buscas (name + phone)
CREATE INDEX IF NOT EXISTS idx_customers_search ON customers(name, phone);

-- ============================================
-- ÍNDICES PARA TABELA PRODUCTS
-- ============================================

-- Índice para busca por status ativo (se existir coluna is_active)
-- CREATE INDEX IF NOT EXISTS idx_products_is_active ON products(is_active);

-- Índice para busca por categoria (se existir coluna category_id)
-- CREATE INDEX IF NOT EXISTS idx_products_category_id ON products(category_id);

-- ============================================
-- VERIFICAÇÃO DE ÍNDICES EXISTENTES
-- ============================================
-- 
-- Para verificar se os índices foram criados, execute:
-- 
-- SHOW INDEX FROM orders;
-- SHOW INDEX FROM order_items;
-- SHOW INDEX FROM customers;
-- 
-- ============================================
-- REMOÇÃO DE ÍNDICES (se necessário)
-- ============================================
-- 
-- Se precisar remover algum índice:
-- 
-- DROP INDEX idx_orders_created_at ON orders;
-- DROP INDEX idx_orders_status ON orders;
-- etc...
-- 
-- ============================================

