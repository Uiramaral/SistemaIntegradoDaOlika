-- ====================================================
-- SQL CORRIGIDO PARA O BANCO DE DADOS ATUAL
-- ====================================================

-- 1.1 Settings - CEP/Número da loja (APÓS business_full_address)
-- NOTA: As colunas business_latitude, business_longitude já existem
--       As colunas delivery_fee_per_km, free_delivery_threshold, max_delivery_distance já existem

ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS store_zip_code VARCHAR(10) NULL AFTER business_full_address,
  ADD COLUMN IF NOT EXISTS store_number VARCHAR(20) NULL AFTER store_zip_code;

-- (Opcional) Campos para PIX estático
ALTER TABLE settings
  ADD COLUMN IF NOT EXISTS pix_key VARCHAR(255) NULL AFTER openai_api_key,
  ADD COLUMN IF NOT EXISTS pix_name VARCHAR(100) NULL AFTER pix_key,
  ADD COLUMN IF NOT EXISTS pix_city VARCHAR(100) NULL AFTER pix_name;

-- NOTAS IMPORTANTES:
-- - addresses.latitude e addresses.longitude já existem (não precisa ALTER)
-- - order_items.custom_name já existe (não precisa ALTER)
-- - As colunas mencionadas acima já existem:
--   * business_latitude, business_longitude (settings)
--   * delivery_fee_per_km, free_delivery_threshold, max_delivery_distance (settings)

-- 1.2 Customer_debts (verificar se já existe)
CREATE TABLE IF NOT EXISTS customer_debts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_id BIGINT UNSIGNED NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  amount DECIMAL(10,2) NOT NULL,
  type ENUM('debit','credit') NOT NULL DEFAULT 'debit',
  status ENUM('open','settled') NOT NULL DEFAULT 'open',
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_customer (customer_id),
  KEY idx_order (order_id),
  CONSTRAINT fk_debts_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_debts_order FOREIGN KEY (order_id) REFERENCES orders(id)
);

