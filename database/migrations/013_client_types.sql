CREATE TABLE IF NOT EXISTS client_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at TEXT DEFAULT (datetime('now'))
);
