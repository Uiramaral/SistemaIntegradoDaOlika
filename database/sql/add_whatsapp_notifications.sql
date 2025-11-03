-- Adiciona campos para controle de notificações de WhatsApp no pagamento e lembrete de não pagamento
ALTER TABLE `orders`
  ADD COLUMN `notified_paid_at` DATETIME NULL AFTER `payment_status`,
  ADD COLUMN `notified_unpaid_at` DATETIME NULL AFTER `notified_paid_at`;
