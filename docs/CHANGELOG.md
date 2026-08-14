# Changelog

## [0.1.0] – 2026-08-14

### Added
- Initiale Projektstruktur (Docker, Apache, PHP 8.2)
- SQLite- und MySQL-Support via `DB_DRIVER`
- 12 Migrationen: Mandanten, Verträge, Nutzer, Sessions, API-Tokens, Dokumente, Benachrichtigungen, Kommunikationslog, Rate-Limiting u.a.
- Multi-Tenant-Architektur mit `user_clients`-Zuordnung
- REST API mit Bearer-Token-Authentifizierung und Rate Limiting
- Web-UI: Dashboard, Verträge, Mandanten, Nutzer, Einstellungen, Backup, Benachrichtigungen
- Login mit CSRF-Schutz, Honeypot und Brute-Force-Schutz
- `bin/create-admin.php` – CLI zum Anlegen des ersten Admins
- GitHub Actions CI/CD für GHCR
