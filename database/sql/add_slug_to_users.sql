-- ============================================
-- ADICIONAR COLUNA SLUG NA TABELA USERS
-- ============================================
-- Este script adiciona suporte a URLs personalizadas (slugs) para multi-tenancy
-- Domínio: {slug}.cozinhapro.app.br
-- Data: 2026-01-27
-- ============================================

-- Verificar se a coluna já existe antes de tentar adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() 
    AND table_name = 'users' 
    AND column_name = 'slug'
);

-- Adicionar coluna slug se não existir
SET @query_add_column = IF(@col_exists = 0, 
    'ALTER TABLE `users` ADD COLUMN `slug` VARCHAR(50) NULL DEFAULT NULL AFTER `email`',
    'SELECT "Coluna slug já existe" AS message'
);

PREPARE stmt FROM @query_add_column;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar índice UNIQUE na coluna slug (se a coluna foi criada)
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE() 
    AND table_name = 'users' 
    AND index_name = 'users_slug_unique'
);

SET @query_add_unique = IF(@index_exists = 0 AND @col_exists = 0, 
    'ALTER TABLE `users` ADD UNIQUE KEY `users_slug_unique` (`slug`)',
    'SELECT "Índice UNIQUE slug já existe ou coluna não foi criada" AS message'
);

PREPARE stmt FROM @query_add_unique;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar índice regular para performance (busca por slug)
SET @index_slug_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE() 
    AND table_name = 'users' 
    AND index_name = 'idx_users_slug'
);

SET @query_add_index = IF(@index_slug_exists = 0 AND @col_exists = 0, 
    'ALTER TABLE `users` ADD KEY `idx_users_slug` (`slug`)',
    'SELECT "Índice idx_users_slug já existe ou coluna não foi criada" AS message'
);

PREPARE stmt FROM @query_add_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- VERIFICAÇÃO FINAL
-- ============================================
SELECT 
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE table_schema = DATABASE() 
            AND table_name = 'users' 
            AND column_name = 'slug'
        ) THEN '✅ Coluna slug criada com sucesso'
        ELSE '❌ Erro: Coluna slug não foi criada'
    END AS status_column,
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE table_schema = DATABASE() 
            AND table_name = 'users' 
            AND index_name = 'users_slug_unique'
        ) THEN '✅ Índice UNIQUE criado'
        ELSE '⚠️ Índice UNIQUE não criado'
    END AS status_unique,
    CASE 
        WHEN EXISTS (
            SELECT 1 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE table_schema = DATABASE() 
            AND table_name = 'users' 
            AND index_name = 'idx_users_slug'
        ) THEN '✅ Índice de performance criado'
        ELSE '⚠️ Índice de performance não criado'
    END AS status_index;

-- ============================================
-- ROLLBACK (caso necessário)
-- ============================================
-- Para reverter as mudanças, execute:
--
-- ALTER TABLE `users` DROP INDEX `idx_users_slug`;
-- ALTER TABLE `users` DROP INDEX `users_slug_unique`;
-- ALTER TABLE `users` DROP COLUMN `slug`;
-- ============================================
