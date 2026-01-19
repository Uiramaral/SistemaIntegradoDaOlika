-- ================================================================
-- CONFIGURAÇÕES MASTER PARA CADASTRO DE ESTABELECIMENTOS
-- ================================================================
-- Este arquivo adiciona configurações editáveis no painel master
-- para controlar parâmetros de cadastro de novos estabelecimentos
-- ================================================================

-- Inserir/Atualizar configurações de cadastro
INSERT INTO master_settings (`key`, `value`, `type`, `description`) VALUES
('registration_trial_days', '14', 'integer', 'Dias de período de teste para novos estabelecimentos'),
('registration_default_commission', '0.49', 'decimal', 'Comissão padrão do Mercado Pago (R$) para novos estabelecimentos'),
('registration_commission_enabled', '1', 'boolean', 'Comissão Mercado Pago habilitada por padrão'),
('registration_default_plan', 'basic', 'string', 'Plano padrão para novos cadastros (basic, ia, custom)'),
('registration_allow_self_signup', '1', 'boolean', 'Permitir auto-cadastro de estabelecimentos (via /register e /cadastro)'),
('registration_require_approval', '0', 'boolean', 'Novos estabelecimentos precisam de aprovação manual do master'),
('registration_notify_master', '1', 'boolean', 'Enviar notificação ao master quando novo estabelecimento se cadastrar'),
('registration_master_email', '', 'string', 'Email do master para receber notificações de novos cadastros')
ON DUPLICATE KEY UPDATE 
    `value` = VALUES(`value`),
    `type` = VALUES(`type`),
    `description` = VALUES(`description`);

-- ================================================================
-- ÍNDICE PARA PERFORMANCE
-- ================================================================
-- Criar índice para busca rápida de configurações (se não existir)
SET @sql = IF((
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'master_settings' 
    AND INDEX_NAME = 'idx_master_settings_key'
) = 0,
'CREATE INDEX idx_master_settings_key ON master_settings(`key`)',
'SELECT "Índice idx_master_settings_key já existe"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- VERIFICAÇÃO FINAL
-- ================================================================
-- Listar todas as configurações de registro
SELECT 
    `key` AS 'Configuração',
    `value` AS 'Valor',
    `type` AS 'Tipo',
    `description` AS 'Descrição'
FROM master_settings
WHERE `key` LIKE 'registration_%'
ORDER BY `key`;

-- ================================================================
-- DOCUMENTAÇÃO
-- ================================================================
/*
CONFIGURAÇÕES ADICIONADAS:

1. registration_trial_days (INTEGER)
   - Valor padrão: 14
   - Controla quantos dias de trial os novos estabelecimentos terão
   - Editável via painel master

2. registration_default_commission (DECIMAL)
   - Valor padrão: 0.49
   - Taxa padrão cobrada por venda via Mercado Pago
   - Editável via painel master

3. registration_commission_enabled (BOOLEAN)
   - Valor padrão: 1 (true)
   - Se false, novos estabelecimentos não terão comissão habilitada por padrão
   - Editável via painel master

4. registration_default_plan (STRING)
   - Valor padrão: 'basic'
   - Plano atribuído quando não especificado no cadastro
   - Valores válidos: basic, ia, custom
   - Editável via painel master

5. registration_allow_self_signup (BOOLEAN)
   - Valor padrão: 1 (true)
   - Se false, desabilita rotas públicas de cadastro
   - Editável via painel master

6. registration_require_approval (BOOLEAN)
   - Valor padrão: 0 (false)
   - Se true, novos estabelecimentos ficam inativos até aprovação manual
   - Editável via painel master

7. registration_notify_master (BOOLEAN)
   - Valor padrão: 1 (true)
   - Se true, envia notificação ao master sobre novos cadastros
   - Editável via painel master

8. registration_master_email (STRING)
   - Valor padrão: vazio
   - Email que receberá notificações de novos cadastros
   - Editável via painel master

------------------------------------------------------------------
COMO USAR NO CÓDIGO:

// Obter trial days configurado no master
$trialDays = MasterSetting::get('registration_trial_days', 14);

// Obter comissão configurada no master
$commission = MasterSetting::get('registration_default_commission', 0.49);

// Verificar se comissão deve ser habilitada
$commissionEnabled = MasterSetting::get('registration_commission_enabled', true);

// Obter plano padrão
$defaultPlan = MasterSetting::get('registration_default_plan', 'basic');

------------------------------------------------------------------
EXEMPLO DE UPDATE VIA PAINEL:

MasterSetting::set('registration_trial_days', 30, 'integer', 'Trial de 30 dias');
MasterSetting::set('registration_default_commission', 0.99, 'decimal', 'Comissão R$ 0,99');

*/
