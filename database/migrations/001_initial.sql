CREATE TABLE IF NOT EXISTS clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL DEFAULT 'privat',
    description TEXT,
    active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS contract_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) DEFAULT '#6366f1'
);
CREATE TABLE IF NOT EXISTS contracts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    contract_number VARCHAR(30) DEFAULT NULL UNIQUE,
    client_id INTEGER NOT NULL,
    category_id INTEGER,
    contract_type VARCHAR(20) NULL,
    title VARCHAR(255) NOT NULL,
    partner VARCHAR(255),
    counterparty_type VARCHAR(20) NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    auto_renewal INTEGER NOT NULL DEFAULT 0,
    minimum_term_months INTEGER NULL DEFAULT NULL,
    renewal_interval_months INTEGER NULL DEFAULT NULL,
    cancellation_period_days INTEGER NULL,
    cancellation_deadline DATE NULL,
    notice_date DATE,
    value DECIMAL(10,2),
    payment_method VARCHAR(20) NULL,
    iban VARCHAR(34) NULL,
    mandate_reference VARCHAR(100) NULL,
    interest_rate DECIMAL(5,2) NULL,
    loan_amount DECIMAL(12,2) NULL,
    monthly_rate DECIMAL(10,2) NULL,
    deductible DECIMAL(10,2) NULL,
    service_interval_months INTEGER NULL,
    billing_interval VARCHAR(20) DEFAULT 'jaehrlich',
    direction VARCHAR(10) NOT NULL DEFAULT 'ausgabe',
    status VARCHAR(20) DEFAULT 'aktiv',
    source VARCHAR(20) DEFAULT 'manuell',
    external_id VARCHAR(100),
    document_path VARCHAR(500),
    notes TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (category_id) REFERENCES contract_categories(id)
);
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(10) DEFAULT 'user',
    active INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INTEGER NOT NULL,
    expires_at TEXT NOT NULL,
    created_at TEXT DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
