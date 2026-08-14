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

- Docker & Docker Compose

## Schnellstart (MySQL)

```bash
cp .env.example .env
# .env anpassen: APP_SECRET, DB_PASSWORD etc. setzen
docker compose up -d
docker compose exec app php database/migrate.php
docker compose exec app php bin/create-admin.php
```

## Schnellstart (SQLite)

```bash
cp .env.example .env
# DB_DRIVER=sqlite und DB_SQLITE_PATH in .env setzen
docker compose -f docker-compose.sqlite.yml up -d
docker compose -f docker-compose.sqlite.yml exec app php database/migrate.php
docker compose -f docker-compose.sqlite.yml exec app php bin/create-admin.php
```

## Lizenz

MIT
