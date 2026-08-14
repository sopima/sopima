# Sopima

Selbst gehostete, open-source Vertragsverwaltung für Einzelpersonen, kleine Organisationen und Teams.

## Funktionen

- Multi-tenant Vertragsverwaltung (CRUD)
- Vertragstypen mit typspezifischen Feldern (Versicherung, Mobilfunk, Darlehen, Sonstiges)
- Dokumentenverwaltung mit mandantengetrennter Speicherung
- Kommunikationsprotokoll pro Vertrag
- Volltextsuche und Ampelstatus
- REST API mit Token-Authentifizierung und Rate Limiting
- Benachrichtigungssystem (SMTP)
- Backup/Restore (JSON-Export/Import)
- Benutzerverwaltung und Einstellungen

## Voraussetzungen

- PHP 8.2+
- Composer
- Apache mit mod_rewrite
- SQLite 3

## Schnellstart

```bash
cp .env.example .env
# APP_SECRET setzen: openssl rand -hex 32
# DB_DRIVER=sqlite und DB_SQLITE_PATH in .env setzen
composer install
php database/migrate.php
php bin/create-admin.php
```

Apache VHost DocumentRoot auf `public/` zeigen lassen, `mod_rewrite` aktivieren.

## Docker

```bash
cp .env.example .env
# .env anpassen
docker compose -f docker-compose.sqlite.yml up -d
docker compose -f docker-compose.sqlite.yml exec app php database/migrate.php
docker compose -f docker-compose.sqlite.yml exec app php bin/create-admin.php
```

## API

Siehe [docs/API.md](docs/API.md) für alle Endpunkte, Authentifizierung und Rate Limiting.

## Lizenz

MIT
