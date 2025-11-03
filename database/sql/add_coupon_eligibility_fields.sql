-- Adicionar campos de elegibilidade aos cupons
ALTER TABLE `coupons`
  ADD COLUMN `first_order_only` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Apenas para primeiro pedido' AFTER `visibility`,
  ADD COLUMN `free_shipping_only` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Apenas para frete grátis (quando há frete no pedido)' AFTER `first_order_only`;

