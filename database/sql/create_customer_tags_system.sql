-- Sistema de Tags para Clientes
-- Permite adicionar tags aos clientes para categorização e filtragem

-- 1. Criar tabela de tags (verificar se já existe)
CREATE TABLE IF NOT EXISTS customer_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nome da tag (ex: Newsletter, VIP, etc)',
    color VARCHAR(7) DEFAULT '#3B82F6' COMMENT 'Cor da tag em hexadecimal',
    description TEXT NULL COMMENT 'Descrição opcional da tag',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_tag_name (name),
    INDEX idx_customer_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Criar tabela pivot para relacionamento muitos-para-muitos entre clientes e tags
CREATE TABLE IF NOT EXISTS customer_tag_pivot (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_customer_tag (customer_id, tag_id),
    INDEX idx_customer_tag_pivot_customer (customer_id),
    INDEX idx_customer_tag_pivot_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Adicionar foreign keys apenas se não existirem
-- Verificar se a foreign key já existe antes de criar
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customer_tag_pivot' 
    AND CONSTRAINT_NAME = 'customer_tag_pivot_customer_id_foreign'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE customer_tag_pivot ADD CONSTRAINT customer_tag_pivot_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE',
    'SELECT "Foreign key customer_id já existe" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customer_tag_pivot' 
    AND CONSTRAINT_NAME = 'customer_tag_pivot_tag_id_foreign'
);

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE customer_tag_pivot ADD CONSTRAINT customer_tag_pivot_tag_id_foreign FOREIGN KEY (tag_id) REFERENCES customer_tags(id) ON DELETE CASCADE',
    'SELECT "Foreign key tag_id já existe" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Inserir algumas tags padrão (ignorar se já existirem)
INSERT IGNORE INTO customer_tags (name, color, description, created_at, updated_at) VALUES
('Newsletter', '#3B82F6', 'Clientes que recebem notificações semanais', NOW(), NOW()),
('VIP', '#F59E0B', 'Clientes VIP com benefícios especiais', NOW(), NOW()),
('Novo Cliente', '#10B981', 'Clientes novos no sistema', NOW(), NOW());

