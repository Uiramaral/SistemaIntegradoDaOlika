-- Adicionar campos de acesso vitalício/gratuito na tabela clients
-- Executado em: 2026-01-18
-- Descrição: Permite marcar estabelecimentos com acesso vitalício gratuito sem necessidade de renovação mensal

-- Verificar e adicionar coluna is_lifetime_free
SET @dbname = DATABASE();
SET @tablename = "clients";
SET @columnname = "is_lifetime_free";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna is_lifetime_free já existe na tabela clients' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Acesso vitalício gratuito (sem renovação)' AFTER is_master;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna lifetime_plan
SET @columnname = "lifetime_plan";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna lifetime_plan já existe na tabela clients' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " ENUM('basic', 'ia', 'custom') NULL COMMENT 'Plano do acesso vitalício' AFTER is_lifetime_free;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna lifetime_reason
SET @columnname = "lifetime_reason";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna lifetime_reason já existe na tabela clients' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(255) NULL COMMENT 'Motivo do acesso vitalício (ex: Fundador, Parceiro, Tester)' AFTER lifetime_plan;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna lifetime_granted_at
SET @columnname = "lifetime_granted_at";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Coluna lifetime_granted_at já existe na tabela clients' AS resultado;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TIMESTAMP NULL COMMENT 'Data de concessão do acesso vitalício' AFTER lifetime_reason;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- ÍNDICES PARA PERFORMANCE
-- ============================================

-- Índice para busca rápida de clientes com acesso vitalício
SET @indexname = "idx_is_lifetime_free";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (index_name = @indexname)
  ) > 0,
  "SELECT 'Índice idx_is_lifetime_free já existe' AS resultado;",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, " (is_lifetime_free);")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- COMENTÁRIOS E DOCUMENTAÇÃO
-- ============================================

/*
CAMPOS ADICIONADOS:

1. is_lifetime_free (TINYINT(1))
   - 0 = Cliente normal (renovação mensal)
   - 1 = Acesso vitalício gratuito
   - Padrão: 0

2. lifetime_plan (ENUM)
   - basic: Plano básico vitalício
   - ia: Plano IA vitalício
   - custom: Plano customizado
   - NULL se não for lifetime

3. lifetime_reason (VARCHAR(255))
   - Motivo/justificativa do acesso vitalício
   - Exemplos: "Fundador", "Parceiro", "Tester Beta", "Cortesia"

4. lifetime_granted_at (TIMESTAMP)
   - Data de concessão do acesso vitalício
   - NULL se não for lifetime

LÓGICA DE NEGÓCIO:

- Clientes com is_lifetime_free = 1 NÃO precisam renovar mensalmente
- O plano ativo é definido por lifetime_plan (não pelo campo plan)
- Todas as features do plano ficam ativas permanentemente
- Não há cobrança e não há expiração
- Comissões do Mercado Pago podem ou não ser aplicadas (configurável)

CASOS DE USO:

1. Estabelecimento fundador (você mesmo)
   UPDATE clients SET 
     is_lifetime_free = 1,
     lifetime_plan = 'ia',
     lifetime_reason = 'Fundador',
     lifetime_granted_at = NOW()
   WHERE id = 1;

2. Parceiro estratégico
   is_lifetime_free = 1
   lifetime_plan = 'ia'
   lifetime_reason = 'Parceiro Estratégico - Contrato #123'

3. Tester beta
   is_lifetime_free = 1
   lifetime_plan = 'basic'
   lifetime_reason = 'Tester Beta - Programa Q1 2026'
*/
