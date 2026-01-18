-- ========================================
-- ADICIONAR COLUNAS DE TEMA NA TABELA SETTINGS
-- Versão: Simplificada e compatível com MySQL 5.7+
-- Data: 2026-01-17
-- ========================================

-- Adicionar colunas de tema (cores)
ALTER TABLE `settings` 
  ADD COLUMN `theme_primary_color` VARCHAR(7) DEFAULT '#f59e0b' COMMENT 'Cor primária (HEX)' AFTER `primary_color`,
  ADD COLUMN `theme_secondary_color` VARCHAR(7) DEFAULT '#8b5cf6' COMMENT 'Cor secundária (HEX)' AFTER `theme_primary_color`,
  ADD COLUMN `theme_accent_color` VARCHAR(7) DEFAULT '#10b981' COMMENT 'Cor de destaque (HEX)' AFTER `theme_secondary_color`,
  ADD COLUMN `theme_background_color` VARCHAR(7) DEFAULT '#ffffff' COMMENT 'Cor de fundo (HEX)' AFTER `theme_accent_color`,
  ADD COLUMN `theme_text_color` VARCHAR(7) DEFAULT '#1f2937' COMMENT 'Cor do texto (HEX)' AFTER `theme_background_color`,
  ADD COLUMN `theme_border_color` VARCHAR(7) DEFAULT '#e5e7eb' COMMENT 'Cor das bordas (HEX)' AFTER `theme_text_color`;

-- Adicionar colunas de branding
ALTER TABLE `settings`
  ADD COLUMN `theme_logo_url` VARCHAR(500) NULL COMMENT 'URL do logo personalizado' AFTER `logo_url`,
  ADD COLUMN `theme_favicon_url` VARCHAR(500) NULL COMMENT 'URL do favicon' AFTER `theme_logo_url`,
  ADD COLUMN `theme_brand_name` VARCHAR(100) NULL COMMENT 'Nome da marca' AFTER `business_name`;

-- Adicionar colunas de estilo
ALTER TABLE `settings`
  ADD COLUMN `theme_font_family` VARCHAR(200) DEFAULT "'Inter', -apple-system, BlinkMacSystemFont, sans-serif" COMMENT 'Família de fontes' AFTER `theme_border_color`,
  ADD COLUMN `theme_border_radius` VARCHAR(20) DEFAULT '12px' COMMENT 'Raio de bordas' AFTER `theme_font_family`,
  ADD COLUMN `theme_shadow_style` VARCHAR(100) DEFAULT '0 4px 12px rgba(0,0,0,0.08)' COMMENT 'Estilo de sombras' AFTER `theme_border_radius`;

-- ========================================
-- VERIFICAÇÃO
-- ========================================
SELECT 
    'Colunas de tema adicionadas com sucesso!' as status,
    COUNT(*) as total_colunas_tema
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'settings' 
  AND COLUMN_NAME LIKE 'theme_%';

-- Listar todas as colunas de tema criadas
SELECT 
    COLUMN_NAME as coluna,
    COLUMN_TYPE as tipo,
    COLUMN_DEFAULT as valor_padrao,
    COLUMN_COMMENT as comentario
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'settings' 
  AND COLUMN_NAME LIKE 'theme_%'
ORDER BY ORDINAL_POSITION;
