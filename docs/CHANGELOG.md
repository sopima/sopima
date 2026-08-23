# Changelog

## [Unreleased]
### Fixed
- Mail template save broken due to `header()` called after HTML output – all POST redirects in SettingsController moved before layout render
- Mail template editor (contenteditable) displayed body as single line – newlines now converted to `<br>` on load, stripped back on save
### Changed
- PDF templates refactored: fixed types (`Datenschutz`, `AGB`, `Vertrag`) replaced by freely named templates per tenant
- PDF templates now collapsible (accordion), individually deletable, new templates can be added via button
- Settings General tab: tab navigation was missing, now correctly rendered

## [0.3.1] – 2026-08-22
### Added
- Per-tenant tabs on dashboard – one tab per Mandant showing the same widgets filtered by `client_id`; overall summary remains as the first tab
- Webhook mail Phase 1: send mail automatically on `POST /api/contracts` if `email` is provided
- Webhook mail Phase 2: configurable mail templates per event (subject + body with placeholders) in Settings
- SMTP test button in Settings
- Contact person (`Ansprechpartner`) manageable via API; contact block moved to dedicated tab in form and detail view
- `ROADMAP.md` with planned features
- WYSIWYG editor (contenteditable + execCommand toolbar) for mail templates and PDF templates
- PDF templates per tenant: upload static PDF files or compose via HTML editor
- PDF attachments on `contract.created` mail – uploaded PDF takes priority, HTML template as fallback (rendered via dompdf)
- `pdf_templates` table with per-tenant, per-type storage (Datenschutz, AGB, Vertrag)
- `clients` table extended with address fields (street, zip, city, email, phone, website)
- PDF template placeholders: contract data, tenant address, contact person data
- Settings tab "PDF-Templates" with per-tenant dropdown and attach toggle

### Fixed
- `GET /api/clients` now correctly filtered by `client_id` when token is bound to a tenant
- `INSERT IGNORE` replaced with `INSERT OR IGNORE` for SQLite compatibility
- Dashboard widget layout and financial calculation corrected
- Backup filename prefix renamed from `contracthub` to `sopima`
- Backup view syntax error in `onchange` attribute fixed

### Docs
- Screenshots added to README
- ROADMAP expanded with additional planned features
- INSTALL and API documentation translated to English

## [0.3.0] – 2026-08-16
### Added
- Internationalization (i18n) – full multilingual support across the entire UI
- Languages: German (de), English (en), Polish (pl)
- Language switching via `APP_LOCALE` in `.env` or directly in the browser via the Settings page
- Setup wizard: language selection in step 2 – seed data (client types, categories) inserted based on selected language
- Settings tab "General" – configure `APP_NAME`, `APP_URL`, `APP_LOCALE` and SMTP directly in the browser
- `CONTRIBUTING.md` – contributor guidelines including translation instructions
- `README.md` in English as primary file, `README.de.md` as German version
- `lang/` directory: add new languages by creating a single PHP file

### Changed
- All views fully migrated to `__()` i18n helper
- Migration seed data removed from SQL files – now inserted language-aware by the setup wizard
- `tokens/show.php` – copy button implementation made more robust
- Project documentation cleaned up: MySQL references removed, SQLite-only documented

### Removed
- Hardcoded German strings from all views, controllers and services

## [0.2.0] – 2026-08-15
### Added
- Plan field added to contract list and edit form
- `APP_NAME` as configurable `.env` variable – all hardcoded app names replaced
- Setup wizard `/setup` – full browser-based initial setup (system check, configuration, migration, admin creation)
- Auto-redirect to `/setup` on first install (no admin present)
- `bin/notify.php` – notification cron job (email, Discord, Telegram, ntfy, Gotify, Pushover, Webhook)
- `bin/entrypoint.sh` – Docker entrypoint with automatic migration on container start

### Changed
- SQLite only – MySQL support fully removed
- `docker-compose.yml` – simplified, no MySQL container
- `.env.example` – cleaned up, SQLite-only variables
- API: contract number prefix now `APP_NAME`-based instead of hardcoded `CH-`
- API: `direction` correctly taken from request body
- API: rate limiting migrated to SQLite `ON CONFLICT`
- All `ON DUPLICATE KEY UPDATE` migrated to SQLite `ON CONFLICT` (ContractController, NotificationController)
- Setup assigns admin automatically to all existing clients

### Removed
- `bin/create-admin.php` – replaced by setup wizard `/setup`
- MySQL support from `db.php`, `migrate.php`, `docker-compose.yml`, `Dockerfile`
- `README_EN.md`, `README_GER.md` – consolidated into `README.md`

## [0.1.0] – 2026-08-14
### Added
- Initial project structure (Apache, PHP 8.2, Composer)
- SQLite support
- 13 migrations: clients, contracts, users, sessions, API tokens, documents, notifications, communication log, rate limiting, client types, etc.
- Multi-tenant architecture with `user_clients` assignment
- REST API with Bearer token authentication and rate limiting
- Web UI: dashboard, contracts, clients, users, settings, backup, notifications
- Login with CSRF protection, honeypot and brute-force protection
- Docker image on GHCR: `ghcr.io/sopima/sopima:latest`
- CI/CD via GitHub Actions
