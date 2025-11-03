-- Script para popular a tabela delivery_distance_pricing com os valores da imagem
-- Se o valor não estiver especificado, usa distância * R$ 1,50

-- Limpar dados existentes (opcional - descomente se quiser limpar antes de inserir)
-- DELETE FROM delivery_distance_pricing WHERE 1=1;

-- Inserir faixas de distância
-- IMPORTANTE: As faixas cobrem intervalos (min_km até max_km), não pontos isolados
INSERT INTO delivery_distance_pricing (min_km, max_km, fee, min_amount_free, is_active, sort_order) VALUES
-- Frete grátis: de 0 até 2.5km
(0.0, 2.5, 0.00, NULL, 1, 1),

-- Faixas específicas com valores fixos (cada uma cobre um intervalo pequeno até o próximo valor)
-- 3km: de 2.51 até 3.0km = R$ 7.00
(2.51, 3.0, 7.00, NULL, 1, 2),

-- 4km: de 3.01 até 4.0km = R$ 7.50
(3.01, 4.0, 7.50, NULL, 1, 3),

-- 5km: de 4.01 até 5.0km = R$ 8.00
(4.01, 5.0, 8.00, NULL, 1, 4),

-- 6km: de 5.01 até 6.0km = R$ 8.50
(5.01, 6.0, 8.50, NULL, 1, 5),

-- 7km: de 6.01 até 7.0km = R$ 9.00
(6.01, 7.0, 9.00, NULL, 1, 6),

-- 8km: de 7.01 até 8.0km = R$ 10.50
(7.01, 8.0, 10.50, NULL, 1, 7),

-- 9km: de 8.01 até 9.0km = R$ 11.00
(8.01, 9.0, 11.00, NULL, 1, 8),

-- 10km: de 9.01 até 10.0km = R$ 11.50
(9.01, 10.0, 11.50, NULL, 1, 9),

-- 11km: não especificado na imagem, usa padrão (11 * 1.50 = 16.50)
-- de 10.01 até 11.0km = R$ 16.50
(10.01, 11.0, 16.50, NULL, 1, 10),

-- 12km: de 11.01 até 12.0km = R$ 15.00
(11.01, 12.0, 15.00, NULL, 1, 11),

-- Para valores acima de 12km e não especificados até 100km: taxa por km de R$ 1,75
-- O sistema detecta faixas grandes (diferença >= 50km) como taxa por km
-- de 12.01 até 99.9km = R$ 1.75 por km
(12.01, 99.9, 1.75, NULL, 1, 12),

-- 100km: Taxa por km de R$ 1,75
-- de 100.0 até 999.9km = R$ 1.75 por km (taxa por km)
(100.0, 999.9, 1.75, NULL, 1, 13);

-- NOTAS IMPORTANTES:
-- 1. Até 2.5km: frete grátis (R$ 0,00)
-- 2. De 2.51km até 12km: valores fixos conforme especificado na imagem
-- 3. De 12.01km até 99.9km: taxa de R$ 1,75 por km
-- 4. De 100km em diante: taxa de R$ 1,75 por km
-- 5. A faixa de 11km não estava na imagem, então foi calculada: 11 * 1.50 = 16.50
-- 
-- O sistema detecta automaticamente "taxa por km" quando a diferença entre max_km - min_km >= 50
-- Para valores exatos (min_km próximo de max_km), usa o valor fixo do campo 'fee'

