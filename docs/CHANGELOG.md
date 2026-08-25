# Changelog

## [0.4.7] - 2026-08-25
### Fixed
- Contract date calculation rewritten: exact day-based arithmetic instead of end-of-month rounding
- End date now correctly = start + term (e.g. 26.03.2026 + 24 months = 26.03.2028)
- Cancellation deadline now correctly = end date - cancellation period in days
- Renewal date now correctly = end date + renewal interval

### Changed
- Contract detail view: "Kündigungsdatum" split into "Kündigungsfrist bis" (deadline) and "Nächste Verlängerung" (renewal date)
- i18n: new keys cd.cancellation_deadline and cd.renewal_date in de/en/pl

## [0.4.6] - 2026-08-25
### Security
- Session cookie `secure` flag now configurable via `APP_SECURE_COOKIE` env variable (default: true)

### Changed
- `.env.example` updated with `APP_SECURE_COOKIE` entry

## [0.4.7] - 2026-08-25
### Fixed
- Contract date calculation rewritten: exact day-based arithmetic instead of end-of-month rounding
- End date now correctly = start + term (e.g. 26.03.2026 + 24 months = 26.03.2028)
- Cancellation deadline now correctly = end date - cancellation period in days
- Renewal date now correctly = end date + renewal interval

### Changed
- Contract detail view: "Kündigungsdatum" split into "Kündigungsfrist bis" (deadline) and "Nächste Verlängerung" (renewal date)
- i18n: new keys cd.cancellation_deadline and cd.renewal_date in de/en/pl

## [0.4.6] - 2026-08-25
### Security
- Session cookie `secure` flag now configurable via `APP_SECURE_COOKIE` env variable (default: true)

### Changed
- `.env.example` updated with `APP_SECURE_COOKIE` entry

## [0.4.5] - 2026-08-25
### Security
- LetterService: deleteTemplate and updateTemplate now enforce tenant check (client_id)
- LetterController: settingsCreate now reads client_id from POST with clientAllowed() check instead of non-existent session key

### Fixed
- MailService: PHP tags in wrapTemplate string literal replaced with concatenation (APP_NAME was rendered as literal text in emails)
- ApiController: PUT /contracts/{id} mail lookup used wrong table `contacts` instead of `contract_contacts`
- NotificationController: INSERT ON CONFLICT execute() had 8 params for 5 placeholders
- DashboardController: duplicate $tabs build block removed (dead code)
- BackupController: insertContract missing fields direction, plan, partner_contract_number, minimum_term_months, renewal_interval_months

## [0.4.4] - 2026-08-25
### Added
- Contracts: pagination with configurable per-page (10 / 25 / 50 / 100 / All)
- Contracts: per-page preference persisted in session
- Contracts: reset button restores default per-page (25)

## [0.4.3] - 2026-08-25
### Added
- Contracts: sortable column headers (title, partner, client, category, status, value, notice date)
- Contracts: sort preference persisted in session

## [0.4.2] - 2026-08-25
### Added
- Contracts: client quick-filter tabs above the contract list (mirrors dashboard behaviour)

### Fixed
- Contracts: status dropdown options were missing closing `>`, making entries invisible
- Dashboard: inactive clients no longer appear as tabs

## [0.4.1] - 2026-08-24
### Fixed
- Settings tab navigation refactored into shared include (letter-templates, backup)

## [0.4.0] - 2026-08-24
### Added
- Letter generator: create, edit, delete letter templates in Settings
- Letter generator: PDF generation via dompdf
- Letter generator: PDF preview with PDF.js (local, build/ only)
- Letter generator: button in contract detail and edit view
- Letter generator: termination letter template as seed (migration 023)
- Letter generator: placeholders {{contract_ref}}, {{partner_contract_number}}, {{external_id}}
- New field `partner_contract_number` on contracts (migration 024)
- `APP_DEBUG` flag in `.env` for PHP error output
- i18n: letter generator strings in de/en/pl

## [0.3.2] – 2026-08-23
### Added
- API: English values for `status` (active/cancelled/expired/paused), `billing_interval` (yearly/monthly/quarterly/biannual), `direction` (expense/income) – mapped internally
- API: additional general fields: `auto_renewal`, `minimum_term_months`, `cancellation_period_days`, `cancellation_deadline`, `payment_method`
- API documentation: complete rewrite with all fields, contact block, mail notification notes, full response examples
- `app/bootstrap.php` – shared bootstrap for CLI scripts
- `bin/cron.php` – central cron runner with auto-discovery of `bin/*.php` jobs
- `bin/notify-expiring.php` – cronjob for contract end-date mail notifications
- Mail events: `contract.updated`, `contract.cancelled`, `contract.expiring` with templates (migration 020)
- `settings` table (migration 021) for persistent app configuration
- `notify_expiring_days` configurable in Settings > General
- PDF templates: freely named, accordion UI, add/delete per tenant (migration 019)
- Mail templates: accordion UI
### Fixed
- API `status` values from external sources (e.g. `active`) normalized to internal values
- Settings General tab: tab navigation was missing
### Changed
- `bin/notify.php` refactored to use `app/bootstrap.php`

## [0.3.2] – 2026-08-23
### Added
- Mail events `contract.updated`, `contract.cancelled`, `contract.expiring` – triggered via API and cronjob
- `bin/notify-expiring.php` – daily cronjob for end-date notifications via mail templates
- `bin/cron.php` – central cron runner with auto-discovery of all `bin/*.php` jobs
- `app/bootstrap.php` – shared bootstrap for CLI scripts
- `notify_expiring_days` configurable in Settings (General tab), stored in `settings` table
- `settings` table (migration 021) for persistent app configuration
- Mail templates for `contract.expiring`, `contract.cancelled`, `contract.updated` (migration 020)
- PDF templates refactored: freely named templates per tenant, accordion UI, add/delete support (migration 019)
- Settings General tab: tab navigation now correctly rendered
### Changed
- `bin/notify.php` refactored to use `app/bootstrap.php` instead of self-contained bootstrap
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
