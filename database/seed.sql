USE event_tiket;

-- Demo accounts
-- admin: admin@demo.test / admin123
-- user : user@demo.test  / user123
INSERT INTO users (nama, email, password_hash, role) VALUES
('Admin', 'admin@demo.test', '$2y$10$DyGxbsokqIkfblOqEqruxOQVQAZ58kH4xO0hv3rziqIRT3RS7HiRi', 'admin'),
('User Demo', 'user@demo.test', '$2y$10$YfSz3vQompryCruOLUqUBuX.k37CQibQcLhbLmb1/2kNENEIlvAqy', 'user');

-- Demo venues
INSERT INTO venue (nama, address, city, capacity) VALUES
('Grand Hall', 'Jl. Merdeka No. 1', 'Bandung', 1500),
('Skyline Convention', 'Jl. Sudirman No. 88', 'Jakarta', 3000);

-- Demo events
INSERT INTO event (venue_id, title, description, event_date, image_path) VALUES
(1, 'Tech Conference 2026', 'Talks, workshop, networking untuk developer.', '2026-06-20 09:00:00', NULL),
(2, 'Music Festival Night', 'Lineup band lokal & guest star.', '2026-07-12 17:00:00', NULL);

-- Demo tickets
INSERT INTO tiket (event_id, nama, price, quota, sold) VALUES
(1, 'Regular Pass', 150000.00, 500, 0),
(1, 'VIP Pass', 450000.00, 100, 0),
(2, 'Festival Regular', 200000.00, 1000, 0),
(2, 'Festival VIP', 650000.00, 150, 0);

-- Demo vouchers
INSERT INTO voucher (code, discount_type, discount_value, quota, used_count, is_active, starts_at, ends_at) VALUES
('HEMAT10', 'percent', 10.00, 50, 0, 1, '2026-04-01 00:00:00', '2026-12-31 23:59:59'),
('POTONG50K', 'amount', 50000.00, 20, 0, 1, '2026-04-01 00:00:00', '2026-12-31 23:59:59'),
('NONAKTIF', 'percent', 20.00, 10, 0, 0, '2026-04-01 00:00:00', '2026-12-31 23:59:59');

