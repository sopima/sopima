-- PDF Templates: type-Spalte entfernt, UNIQUE-Constraint weg, freie Titel
CREATE TABLE IF NOT EXISTS pdf_templates_new (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id  INTEGER NOT NULL,
    title      VARCHAR(255) NOT NULL DEFAULT '',
    body       TEXT NOT NULL DEFAULT '',
    attach     INTEGER NOT NULL DEFAULT 0,
    active     INTEGER NOT NULL DEFAULT 1,
    file_path  VARCHAR(500) DEFAULT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

INSERT INTO pdf_templates_new (id, client_id, title, body, attach, active, file_path, created_at, updated_at)
    SELECT id, client_id, COALESCE(NULLIF(title,''), type), body, attach, active, file_path, created_at, updated_at
    FROM pdf_templates;

DROP TABLE pdf_templates;
ALTER TABLE pdf_templates_new RENAME TO pdf_templates;
