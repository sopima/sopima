CREATE TABLE IF NOT EXISTS client_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at TEXT DEFAULT (datetime('now'))
);
INSERT INTO client_types (name) VALUES
('Privat'),
('Firma'),
('WEG'),
('Verein');
