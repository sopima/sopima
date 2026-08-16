# Sopima

> ⚠️ **Alpha-Stadium** – Sopima befindet sich in aktiver Entwicklung. Funktionen können sich ändern, Fehler sind möglich. Nicht für produktionskritische Daten empfohlen. Feedback willkommen.

Selbst gehostete, open-source Vertragsverwaltung für Einzelpersonen, kleine Organisationen und Teams.

## Funktionen

- Multi-tenant Vertragsverwaltung (CRUD)
- Vertragstypen mit typspezifischen Feldern (Versicherung, Mobilfunk, Darlehen, Sonstiges)
- Dokumentenverwaltung mit mandantengetrennter Speicherung
- Kommunikationsprotokoll pro Vertrag
- Volltextsuche und Ampelstatus
- REST API mit Token-Authentifizierung und Rate Limiting
- Benachrichtigungssystem (E-Mail, Discord, Telegram, ntfy, Gotify, Pushover, Webhook)
- Backup/Restore (JSON-Export/Import)
- Benutzerverwaltung und Einstellungen

## Voraussetzungen

- PHP 8.2+
- PHP-Extension: `pdo_sqlite`
- Composer
- Apache mit `mod_rewrite`

## Schnellstart

```bash
git clone https://github.com/sopima/sopima
cd sopima
cp .env.example .env
composer install --no-dev
```

Apache VHost DocumentRoot auf `public/` zeigen lassen, `mod_rewrite` aktivieren.

Dann im Browser `https://your-domain.example.com/setup` aufrufen – der Setup-Wizard führt durch Konfiguration, Datenbank-Migration und Anlegen des ersten Admins.

## Docker

```bash
docker compose up -d
```

Danach `http://localhost:8080/setup` aufrufen.

## API

Siehe [docs/API.md](docs/API.md) für alle Endpunkte, Authentifizierung und Rate Limiting.

## Mitmachen

Sopima ist ein Community-Projekt. Issues und Pull Requests sind willkommen.
Bitte vor größeren Änderungen kurz ein Issue öffnen.

## Benachrichtigungen (Cronjob)

Bei direkter PHP/Apache-Installation: `bin/notify.php` als Cronjob einrichten:

```
0 8 * * * php /path/to/sopima/bin/notify.php >> /var/log/sopima_notify.log 2>&1
```

Im Docker-Betrieb kann ein separater Cronjob-Container oder ein Host-Cronjob genutzt werden.

## Lizenz

MIT
