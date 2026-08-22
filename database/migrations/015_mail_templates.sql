CREATE TABLE IF NOT EXISTS mail_templates (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    event     VARCHAR(64) NOT NULL UNIQUE,
    subject   TEXT NOT NULL,
    body      TEXT NOT NULL,
    active    INTEGER NOT NULL DEFAULT 1,
    updated_at TEXT DEFAULT (datetime('now'))
);

INSERT OR IGNORE INTO mail_templates (event, subject, body) VALUES (
    'contract.created',
    'Neuer Vertrag: {{title}}',
    'Hallo,

ein neuer Vertrag wurde angelegt.

Titel:      {{title}}
Partner:    {{partner}}
Beginn:     {{start_date}}
Ende:       {{end_date}}
Kündigung:  {{notice_date}}
Wert:       {{value}}
Intervall:  {{billing_interval}}
Status:     {{status}}

Viele Grüße
{{app_name}}'
);
