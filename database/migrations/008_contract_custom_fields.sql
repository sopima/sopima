CREATE TABLE IF NOT EXISTS contract_custom_fields (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contract_id INTEGER NOT NULL,
    label VARCHAR(100) NOT NULL,
    value TEXT NULL,
    field_type VARCHAR(10) NOT NULL DEFAULT 'text',
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE
);
