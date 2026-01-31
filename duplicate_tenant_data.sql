-- SCRIPT DE DUPLICAÇÃO DE DADOS PARA NOVO CLIENTE (TENANT)
-- VERSÃO CORRIGIDA (PRODUCTS COLUMNS)
-- Duplica dados do Client 1 para Client 11

SET @source_client_id = 1;
SET @target_client_id = 11;

-- 1. Garantir Client 11
INSERT IGNORE INTO clients (id, name, slug, email, active, created_at, updated_at)
VALUES (@target_client_id, 'Ambiente de Teste', 'teste-duplicado', 'teste@duplicado.com', 1, NOW(), NOW());

-- 2. DUPLICAR CONFIGURAÇÕES GERAIS (Settings)
UPDATE settings s_target
JOIN settings s_source ON s_source.client_id = @source_client_id
SET 
    s_target.business_name = s_source.business_name,
    s_target.business_description = s_source.business_description,
    s_target.business_phone = s_source.business_phone,
    s_target.business_email = s_source.business_email,
    s_target.business_address = s_source.business_address,
    s_target.business_full_address = s_source.business_full_address,
    s_target.business_latitude = s_source.business_latitude,
    s_target.business_longitude = s_source.business_longitude,
    s_target.is_open = s_source.is_open,
    s_target.primary_color = s_source.primary_color,
    s_target.logo_url = s_source.logo_url,
    s_target.header_image_url = s_source.header_image_url,
    s_target.min_delivery_value = s_source.min_delivery_value,
    s_target.free_delivery_threshold = s_source.free_delivery_threshold,
    s_target.delivery_fee_per_km = s_source.delivery_fee_per_km,
    s_target.max_delivery_distance = s_source.max_delivery_distance,
    s_target.mercadopago_access_token = s_source.mercadopago_access_token,
    s_target.mercadopago_public_key = s_source.mercadopago_public_key,
    s_target.mercadopago_env = s_source.mercadopago_env,
    s_target.google_maps_api_key = s_source.google_maps_api_key,
    s_target.openai_api_key = s_source.openai_api_key,
    s_target.whatsapp_api_url = s_source.whatsapp_api_url,
    s_target.whatsapp_api_key = s_source.whatsapp_api_key,
    s_target.loyalty_enabled = s_source.loyalty_enabled,
    s_target.loyalty_points_per_real = s_source.loyalty_points_per_real,
    s_target.cashback_percentage = s_source.cashback_percentage,
    s_target.order_cutoff_time = s_source.order_cutoff_time,
    s_target.advance_order_days = s_source.advance_order_days,
    s_target.theme_primary_color = s_source.theme_primary_color,
    s_target.theme_secondary_color = s_source.theme_secondary_color,
    s_target.theme_accent_color = s_source.theme_accent_color,
    s_target.theme_background_color = s_source.theme_background_color,
    s_target.theme_text_color = s_source.theme_text_color,
    s_target.theme_border_color = s_source.theme_border_color,
    s_target.theme_logo_url = s_source.theme_logo_url,
    s_target.theme_favicon_url = s_source.theme_favicon_url,
    s_target.theme_brand_name = s_source.theme_brand_name,
    s_target.theme_font_family = s_source.theme_font_family,
    s_target.theme_border_radius = s_source.theme_border_radius,
    s_target.theme_shadow_style = s_source.theme_shadow_style,
    s_target.sales_multiplier = s_source.sales_multiplier,
    s_target.resale_multiplier = s_source.resale_multiplier,
    s_target.fixed_cost = s_source.fixed_cost,
    s_target.tax_percentage = s_source.tax_percentage,
    s_target.card_fee_percentage = s_source.card_fee_percentage,
    s_target.updated_at = NOW()
WHERE s_target.client_id = @target_client_id;

INSERT INTO settings (
    client_id, business_name, business_description, business_phone, business_email, 
    business_address, business_full_address, business_latitude, business_longitude, 
    is_open, primary_color, logo_url, header_image_url, min_delivery_value, 
    free_delivery_threshold, delivery_fee_per_km, max_delivery_distance, 
    mercadopago_access_token, mercadopago_public_key, mercadopago_env, 
    google_maps_api_key, openai_api_key, whatsapp_api_url, whatsapp_api_key, 
    loyalty_enabled, loyalty_points_per_real, cashback_percentage, order_cutoff_time, 
    advance_order_days, theme_primary_color, theme_secondary_color, theme_accent_color, 
    theme_background_color, theme_text_color, theme_border_color, theme_logo_url, 
    theme_favicon_url, theme_brand_name, theme_font_family, theme_border_radius, 
    theme_shadow_style, sales_multiplier, resale_multiplier, fixed_cost, 
    tax_percentage, card_fee_percentage, created_at, updated_at
)
SELECT 
    @target_client_id, business_name, business_description, business_phone, business_email, 
    business_address, business_full_address, business_latitude, business_longitude, 
    is_open, primary_color, logo_url, header_image_url, min_delivery_value, 
    free_delivery_threshold, delivery_fee_per_km, max_delivery_distance, 
    mercadopago_access_token, mercadopago_public_key, mercadopago_env, 
    google_maps_api_key, openai_api_key, whatsapp_api_url, whatsapp_api_key, 
    loyalty_enabled, loyalty_points_per_real, cashback_percentage, order_cutoff_time, 
    advance_order_days, theme_primary_color, theme_secondary_color, theme_accent_color, 
    theme_background_color, theme_text_color, theme_border_color, theme_logo_url, 
    theme_favicon_url, theme_brand_name, theme_font_family, theme_border_radius, 
    theme_shadow_style, sales_multiplier, resale_multiplier, fixed_cost, 
    tax_percentage, card_fee_percentage, NOW(), NOW()
FROM settings WHERE client_id = @source_client_id
AND NOT EXISTS (SELECT 1 FROM settings WHERE client_id = @target_client_id);

-- 3. DUPLICAR CONFIGURAÇÕES DE PAGAMENTO (PaymentSettings)
INSERT IGNORE INTO payment_settings (client_id, `key`, value, description, is_active, created_at, updated_at)
SELECT @target_client_id, `key`, value, description, is_active, NOW(), NOW()
FROM payment_settings WHERE client_id = @source_client_id;

-- 4. DUPLICAR CATEGORIAS (Categories)
INSERT INTO categories (
    client_id, name, description, image_url, is_active, sort_order, display_type, created_at, updated_at
)
SELECT 
    @target_client_id, name, description, image_url, is_active, sort_order, display_type, NOW(), NOW()
FROM categories WHERE client_id = @source_client_id;

-- 5. DUPLICAR PRODUTOS (Products)
-- Removido: wholesale_only
-- Adicionado: variants, allergens, uses_baker_percentage
INSERT INTO products (
    client_id, category_id, name, sku, price, stock, is_active, 
    show_in_catalog, gluten_free, contamination_risk, cover_image, description, 
    label_description, seo_title, seo_description, image_url, is_featured, 
    is_available, preparation_time, nutritional_info, sort_order, weight_grams, 
    variants, allergens, uses_baker_percentage,
    created_at, updated_at
)
SELECT 
    @target_client_id, category_id, name, sku, price, stock, is_active, 
    show_in_catalog, gluten_free, contamination_risk, cover_image, description, 
    label_description, seo_title, seo_description, image_url, is_featured, 
    is_available, preparation_time, nutritional_info, sort_order, weight_grams, 
    variants, allergens, uses_baker_percentage,
    NOW(), NOW()
FROM products WHERE client_id = @source_client_id;

-- 6. DUPLICAR CLIENTES (Customers)
INSERT IGNORE INTO customers (
    client_id, visitor_id, name, phone, preferred_gateway_phone, email, address, 
    neighborhood, city, state, zip_code, custom_delivery_fee, custom_delivery_note, 
    birth_date, preferences, password, cpf, is_active, is_wholesale, total_debts, 
    newsletter, created_at, updated_at
)
SELECT 
    @target_client_id, 
    CONCAT(visitor_id, '-copy'), 
    name, 
    phone,
    preferred_gateway_phone, 
    email,
    address, 
    neighborhood, city, state, zip_code, custom_delivery_fee, custom_delivery_note, 
    birth_date, preferences, password, cpf, is_active, is_wholesale, total_debts, 
    newsletter, NOW(), NOW()
FROM customers WHERE client_id = @source_client_id;

-- 7. DUPLICAR TAXAS DE ENTREGA (DeliveryFees)
INSERT INTO delivery_fees (
    client_id, name, description, base_fee, fee_per_km, minimum_order_value, 
    free_delivery_threshold, max_distance_km, is_active, delivery_time_minutes, 
    created_at, updated_at
)
SELECT 
    @target_client_id, name, description, base_fee, fee_per_km, minimum_order_value, 
    free_delivery_threshold, max_distance_km, is_active, delivery_time_minutes, 
    NOW(), NOW()
FROM delivery_fees WHERE client_id = @source_client_id
ON DUPLICATE KEY UPDATE name = CONCAT(VALUES(name), ' (Copy)'); 

SELECT 'Duplicação Concluída com Sucesso!' as status;
