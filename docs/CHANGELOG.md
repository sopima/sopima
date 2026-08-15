# Changelog

## [0.2.0] – 2026-08-15

### Added
- Tarif-Feld (plan) in Vertragsübersicht und Bearbeiten-Formular ergänzt
- `APP_NAME` als konfigurierbare `.env`-Variable – alle hardcodierten App-Namen ersetzt
- Setup-Wizard `/setup` – Ersteinrichtung komplett im Browser (Systemprüfung, Konfiguration, Migration, Admin anlegen)
- Auto-Redirect zu `/setup` bei Erstinstallation (kein Admin vorhanden)
- `bin/notify.php` – Benachrichtigungs-Cron-Job (E-Mail, Discord, Telegram, ntfy, Gotify, Pushover, Webhook)
- `bin/entrypoint.sh` – Docker Entrypoint mit automatischer Migration beim Container-Start

### Changed
- SQLite only – MySQL-Support vollständig entfernt
- `docker-compose.yml` – vereinfacht, kein MySQL-Container mehr
- `.env.example` – bereinigt, nur noch SQLite-relevante Variablen
- API: Vertragsnummer-Präfix jetzt `APP_NAME`-basiert statt hardcodiert `CH-`
- API: `direction` wird korrekt aus Request-Body übernommen
- API: Rate-Limit auf SQLite `ON CONFLICT` umgestellt
- Alle `ON DUPLICATE KEY UPDATE` auf SQLite `ON CONFLICT` umgestellt (ContractController, NotificationController)
- Setup weist Admin automatisch allen bestehenden Mandanten zu

### Removed
- `bin/create-admin.php` – ersetzt durch Setup-Wizard `/setup`
- MySQL-Support aus `db.php`, `migrate.php`, `docker-compose.yml`, `Dockerfile`
- `README_EN.md`, `README_GER.md` – konsolidiert in `README.md`

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

