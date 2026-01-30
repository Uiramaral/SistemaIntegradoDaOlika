-- Adiciona coluna mark_for_print à tabela production_list_items
-- Por padrão, todos os itens são marcados para impressão (true)
-- Data: 2026-01-25

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'production_list_items'
    AND COLUMN_NAME = 'mark_for_print'
);

-- Adicionar coluna apenas se não existir
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `production_list_items` 
     ADD COLUMN `mark_for_print` TINYINT(1) NOT NULL DEFAULT 1 
     COMMENT ''Incluir na fila de impressão; padrão true''
     AFTER `observation`',
    'SELECT ''Coluna mark_for_print já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar registros existentes para marcar todos como true (caso a coluna já existisse com valores diferentes)
UPDATE `production_list_items` 
SET `mark_for_print` = 1 
WHERE `mark_for_print` IS NULL OR `mark_for_print` = 0;
