# Changelog

## [0.5.2] - 2026-08-25
### Added
- Backup: optional AES-256-CBC encryption for JSON export – enter a password to download a `.enc` file
- Backup: encrypted `.enc` files can be restored by providing the password on import
- Backup: CSV export now shows "no password protection" notice
### Fixed
- Backup: CSV export `fputcsv()` deprecation warning resolved (explicit escape parameter)
- Backup: nested arrays in CSV rows no longer cause "Array to string conversion" warnings

## [0.5.1] - 2026-08-25
### Added
- API tokens: last IP and last endpoint logged on every request (migrations 027, 028)
- API tokens: "Last IP" and "Last Endpoint" columns in token list UI
- CI: `composer audit` security job runs on every push to main
### Fixed
- Settings: PDF template upload now validates MIME type (PDF only) and file size (max 20 MB); random filename instead of predictable ID+timestamp
- i18n: missing key `settings.pdf.empty` added in de/en/pl

## [0.5.0] - 2026-08-25
### Security
- HTTP security headers added: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy`
- API: `GET /api/clients` now enforces tenant isolation – tokens without `client_id` receive 403 unless role is admin

## [0.4.9] - 2026-08-25
### Added
- Contracts: partner autocomplete in create/edit form – suggests known partners and prefills customer number and contact data
- Contracts: hint shown when contact data is adopted from existing contract

## [0.4.8] - 2026-08-25
### Added
- Contracts: new field `customer_number` (migration 025) for grouping contracts under one customer account
- Contracts: new field `phone_number` (migration 026) for telecommunication contracts
- Letter generator: new placeholders {{customer_number}}, {{phone_number}}, {{title}}, {{plan}}, {{partner_address_block}}, {{partner_company}}, {{partner_street}}, {{partner_zip}}, {{partner_city}}, {{cancellation_deadline}}
- Letter generator: dates now formatted as dd.mm.yyyy in PDF output
- Letter generator: two default templates – "Kündigung Mobilfunk/Internet" and "Kündigung Allgemein"
### Fixed
- Letter generator: partner address loaded from contract_contacts instead of clients
- Letter generator: {{title}} and {{customer_number}} placeholders were missing

## [0.4.7] - 2026-08-25
### Fixed
- Contract date calculation rewritten: exact day-based arithmetic instead of end-of-month rounding
- End date now correctly = start + term (e.g. 26.03.2026 + 24 months = 26.03.2028)
- Cancellation deadline now correctly = end date - cancellation period in days
- Renewal date now correctly = end date + renewal interval
### Changed
- Contract detail view: "Kündigungsdatum" split into "Kündigungsfrist bis" (deadline) and "Nächste Verlängerung" (renewal date)
- Detail banner now uses cancellation_deadline for countdown and notice_date for renewal display
- "Bei Kündigung heute" calculation rewritten to exact day-based arithmetic
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

## [0.3.2] - 2026-08-23
### Added
- API: English values for `status`, `billing_interval`, `direction` – mapped internally
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
- API `status` values from external sources normalized to internal values
- Settings General tab: tab navigation was missing
- Mail template save broken due to `header()` called after HTML output
- Mail template editor: newlines now converted to `<br>` on load, stripped back on save
### Changed
- `bin/notify.php` refactored to use `app/bootstrap.php`
- PDF templates refactored: fixed types replaced by freely named templates per tenant, collapsible accordion UI

## [0.3.1] - 2026-08-22
### Added
- Per-tenant tabs on dashboard
- Webhook mail Phase 1: send mail automatically on `POST /api/contracts`
- Webhook mail Phase 2: configurable mail templates per event in Settings
- SMTP test button in Settings
- Contact person manageable via API; contact block moved to dedicated tab
- `ROADMAP.md`
- WYSIWYG editor for mail templates and PDF templates
- PDF templates per tenant with dompdf rendering
- `clients` table extended with address fields
- Settings tab "PDF-Templates"
### Fixed
- `GET /api/clients` filtered by `client_id` when token is bound to a tenant
- `INSERT IGNORE` replaced with `INSERT OR IGNORE` for SQLite compatibility
- Dashboard widget layout and financial calculation corrected
- Backup filename prefix renamed from `contracthub` to `sopima`
### Docs
- Screenshots added to README
- INSTALL and API documentation translated to English

## [0.3.0] - 2026-08-16
### Added
- Internationalization (i18n) – full multilingual support (de, en, pl)
- Language switching via `APP_LOCALE` or Settings page
- Setup wizard: language selection with language-aware seed data
- Settings tab "General" – configure APP_NAME, APP_URL, APP_LOCALE, SMTP in browser
- `CONTRIBUTING.md` with translation instructions
- `README.md` in English, `README.de.md` in German
### Changed
- All views migrated to `__()` i18n helper
- Migration seed data removed from SQL – inserted language-aware by setup wizard
### Removed
- Hardcoded German strings from all views, controllers and services

## [0.2.0] - 2026-08-15
### Added
- Plan field in contract list and edit form
- `APP_NAME` as configurable `.env` variable
- Setup wizard `/setup` – browser-based initial setup
- Auto-redirect to `/setup` on first install
- `bin/notify.php` – notification cron job (email, Discord, Telegram, ntfy, Gotify, Pushover, Webhook)
- `bin/entrypoint.sh` – Docker entrypoint with automatic migration
### Changed
- SQLite only – MySQL support fully removed
- API: contract number prefix now APP_NAME-based
- API: rate limiting migrated to SQLite ON CONFLICT
### Removed
- `bin/create-admin.php` – replaced by setup wizard
- MySQL support

## [0.1.0] - 2026-08-14
### Added
- Initial project structure (Apache, PHP 8.2, Composer)
- SQLite support with 13 migrations
- Multi-tenant architecture with `user_clients` assignment
- REST API with Bearer token authentication and rate limiting
- Web UI: dashboard, contracts, clients, users, settings, backup, notifications
- Login with CSRF protection, honeypot and brute-force protection
- Docker image on GHCR: `ghcr.io/sopima/sopima:latest`
- CI/CD via GitHub Actions