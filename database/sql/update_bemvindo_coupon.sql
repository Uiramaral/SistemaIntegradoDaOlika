-- Atualizar cupom BEMVINDO para ser apenas para primeiro pedido
UPDATE `coupons`
SET `first_order_only` = 1
WHERE `code` = 'BEMVINDO';
