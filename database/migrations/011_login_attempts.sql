CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip VARCHAR(45) NOT NULL,
    attempted_at TEXT NOT NULL DEFAULT (datetime('now'))
);
