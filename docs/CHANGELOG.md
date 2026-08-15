# Changelog

## [Unreleased]

### Added
- `APP_NAME` als konfigurierbare `.env`-Variable – alle hardcodierten App-Namen ersetzt
- Migration 014: `minimum_term_months`, `renewal_interval_months`
- Migration 015: `contract_direction`
- `bin/notify.php` – Benachrichtigungs-Cron-Job (E-Mail, Discord, Telegram, ntfy, Gotify, Pushover, Webhook)

### Changed
- SQLite only – MySQL-Support entfernt
- `docker-compose.yml` – vereinfacht, kein MySQL-Container mehr
- `.env.example` – bereinigt, nur noch SQLite-relevante Variablen
- `docs/INSTALL.md` – Setup-Wizard dokumentiert

### Removed
- `bin/create-admin.php` – ersetzt durch Setup-Wizard `/setup`
- MySQL-Support aus `db.php`, `migrate.php`, `docker-compose.yml`

## [0.1.0] – 2026-08-14

### Added
- Initiale Projektstruktur (Apache, PHP 8.2, Composer)
- SQLite-Support
- 13 Migrationen: Mandanten, Verträge, Nutzer, Sessions, API-Tokens, Dokumente, Benachrichtigungen, Kommunikationslog, Rate-Limiting, Mandantentypen u.a.
- Multi-Tenant-Architektur mit `user_clients`-Zuordnung
- REST API mit Bearer-Token-Authentifizierung und Rate Limiting
- Web-UI: Dashboard, Verträge, Mandanten, Nutzer, Einstellungen, Backup, Benachrichtigungen
- Login mit CSRF-Schutz, Honeypot und Brute-Force-Schutz
- Docker Image auf GHCR: `ghcr.io/sopima/sopima:latest`
- CI/CD via GitHub Actions
