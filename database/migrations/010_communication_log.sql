CREATE TABLE IF NOT EXISTS contract_communication_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contract_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    logged_at TEXT NOT NULL DEFAULT (datetime('now')),
    channel VARCHAR(20) NOT NULL DEFAULT 'sonstig',
    direction VARCHAR(20) NOT NULL DEFAULT 'ausgehend',
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
