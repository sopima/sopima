# Roadmap

This document outlines planned features and improvements for future Sopima releases.
The order reflects priority, not a fixed release schedule.

---

## Planned Features

### API Webhook Mail *(next)*

When a new contract is created via the API (`POST /api/contracts`), Sopima automatically sends a confirmation email to an address provided in the request body.

**Use case:** External applications (e.g. a registration form) create contracts via API and trigger a mail to the end user - including attachments like privacy policy, SEPA mandate or the contract itself.

**Phase 1 - Basic mail on contract creation**
- Accept `email` field in `POST /api/contracts` request body
- Send confirmation mail via existing `MailService` (SMTP)
- Simple hardcoded template as starting point

**Phase 2 - Configurable mail templates**
- New `mail_templates` table in the database
- Templates configurable per event (e.g. `contract.created`) in the Settings UI
- Placeholder support: `{{title}}`, `{{partner}}`, `{{start_date}}`, `{{contract_number}}`, `{{email}}`

**Phase 3 - PDF attachments**
- On-the-fly PDF generation from contract data (via mPDF or FPDF)
- Attach generated PDFs to the mail (e.g. privacy policy, SEPA mandate, contract summary)
- PDF templates configurable per client/token

---

### Dashboard: Client Tabs
- Overall summary as first tab
- One tab per client, each showing the same widgets filtered by `client_id`
- Designed to be extensible

---

### Tauri Desktop Build
- No-Docker distribution for desktop environments
- Single binary, no server setup required

---

## Completed

See [CHANGELOG.md](CHANGELOG.md) for all released features.