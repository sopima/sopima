# Sopima

Open-source, self-hosted contract management for individuals, small organizations and teams.

## Features

- Multi-tenant contract management (CRUD)
- Contract types with type-specific fields (insurance, mobile, loan, other)
- Document management with tenant-separated storage
- Communication log per contract
- Full-text search and traffic-light status
- REST API with token authentication and rate limiting
- Notification system (SMTP)
- Backup/Restore (JSON export/import)
- User management and settings

## Requirements

- Docker & Docker Compose

## Quick Start (MySQL)

```bash
cp .env.example .env
# Edit .env and set APP_SECRET, DB_PASSWORD etc.
docker compose up -d
docker compose exec app php database/migrate.php
docker compose exec app php bin/create-admin.php
```

## Quick Start (SQLite)

```bash
cp .env.example .env
# Set DB_DRIVER=sqlite and DB_SQLITE_PATH in .env
docker compose -f docker-compose.sqlite.yml up -d
docker compose -f docker-compose.sqlite.yml exec app php database/migrate.php
docker compose -f docker-compose.sqlite.yml exec app php bin/create-admin.php
```

## License

MIT
