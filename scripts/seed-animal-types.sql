-- Crear tipos de animales básicos
INSERT INTO animal_types (name, description, created_at, updated_at) VALUES
('Bovino', 'Ganado bovino para carne y leche', NOW(), NOW()),
('Porcino', 'Ganado porcino para producción de carne', NOW(), NOW()),
('Ovino', 'Ganado ovino para carne y lana', NOW(), NOW()),
('Caprino', 'Ganado caprino para carne y leche', NOW(), NOW()),
('Equino', 'Caballos y yeguas', NOW(), NOW());

-- Crear datos de ejemplo para historial de precios
INSERT INTO price_histories (animal_type_id, date, average_price_ars, average_price_usd, market_trend, weight_range_min, weight_range_max, age_range_min, age_range_max, created_at, updated_at) VALUES
(1, '2024-01-01', 45000, 180, 'stable', 350, 450, 18, 36, NOW(), NOW()),
(1, '2024-02-01', 47000, 188, 'rising', 350, 450, 18, 36, NOW(), NOW()),
(1, '2024-03-01', 46500, 186, 'stable', 350, 450, 18, 36, NOW(), NOW()),
(1, '2024-04-01', 48000, 192, 'rising', 350, 450, 18, 36, NOW(), NOW()),
(1, '2024-05-01', 49500, 198, 'rising', 350, 450, 18, 36, NOW(), NOW()),
(1, '2024-06-01', 51000, 204, 'rising', 350, 450, 18, 36, NOW(), NOW()),

(2, '2024-01-01', 35000, 140, 'stable', 80, 120, 6, 12, NOW(), NOW()),
(2, '2024-02-01', 36000, 144, 'rising', 80, 120, 6, 12, NOW(), NOW()),
(2, '2024-03-01', 35500, 142, 'stable', 80, 120, 6, 12, NOW(), NOW()),
(2, '2024-04-01', 37000, 148, 'rising', 80, 120, 6, 12, NOW(), NOW()),
(2, '2024-05-01', 38000, 152, 'rising', 80, 120, 6, 12, NOW(), NOW()),
(2, '2024-06-01', 39000, 156, 'rising', 80, 120, 6, 12, NOW(), NOW()),

(3, '2024-01-01', 25000, 100, 'stable', 40, 60, 8, 18, NOW(), NOW()),
(3, '2024-02-01', 26000, 104, 'rising', 40, 60, 8, 18, NOW(), NOW()),
(3, '2024-03-01', 25500, 102, 'stable', 40, 60, 8, 18, NOW(), NOW()),
(3, '2024-04-01', 27000, 108, 'rising', 40, 60, 8, 18, NOW(), NOW()),
(3, '2024-05-01', 28000, 112, 'rising', 40, 60, 8, 18, NOW(), NOW()),
(3, '2024-06-01', 29000, 116, 'rising', 40, 60, 8, 18, NOW(), NOW()),

(4, '2024-01-01', 20000, 80, 'stable', 30, 50, 6, 15, NOW(), NOW()),
(4, '2024-02-01', 21000, 84, 'rising', 30, 50, 6, 15, NOW(), NOW()),
(4, '2024-03-01', 20500, 82, 'stable', 30, 50, 6, 15, NOW(), NOW()),
(4, '2024-04-01', 22000, 88, 'rising', 30, 50, 6, 15, NOW(), NOW()),
(4, '2024-05-01', 23000, 92, 'rising', 30, 50, 6, 15, NOW(), NOW()),
(4, '2024-06-01', 24000, 96, 'rising', 30, 50, 6, 15, NOW(), NOW());
