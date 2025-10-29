-- Adicionar campos para armazenar informações detalhadas de desconto e cupom
-- Data: 2025-01-15

ALTER TABLE `orders` 
ADD COLUMN `discount_type` ENUM('percentage', 'fixed', 'coupon') NULL DEFAULT NULL COMMENT 'Tipo de desconto aplicado' AFTER `discount_amount`,
ADD COLUMN `discount_original_value` DECIMAL(10, 2) NULL DEFAULT NULL COMMENT 'Valor original do desconto (percentual ou fixo) antes do cálculo' AFTER `discount_type`,
ADD COLUMN `manual_discount_type` ENUM('percentage', 'fixed') NULL DEFAULT NULL COMMENT 'Tipo do desconto manual (se aplicado separadamente do cupom)' AFTER `discount_original_value`,
ADD COLUMN `manual_discount_value` DECIMAL(10, 2) NULL DEFAULT NULL COMMENT 'Valor do desconto manual (se aplicado separadamente)' AFTER `manual_discount_type`;

-- Atualizar registros existentes se necessário
UPDATE `orders` 
SET `discount_type` = 'coupon' 
WHERE `coupon_code` IS NOT NULL AND `discount_amount` > 0;

UPDATE `orders` 
SET `discount_type` = 'percentage' 
WHERE `coupon_code` IS NULL AND `discount_amount` > 0 AND `discount_type` IS NULL;

