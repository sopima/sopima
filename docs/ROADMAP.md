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

### Cancellation Deadline Overview
- Dedicated page listing all contracts with calculated cancellation deadlines
- Sortable by urgency, filterable by client, time range (30/60/90 days), contract type
- Columns: contract name, partner, client, end date, notice period, cancel-by date, status
- Cancel-by date calculated automatically (end date minus notice period)
- No new database fields required - purely calculated from existing data
- Exportable as CSV/PDF

---

### Price History per Contract
- Track historical price changes per contract (date, amount, optional note)
- Table view + line chart (Chart.js)
- Useful for recurring contracts: utilities, insurance, SaaS subscriptions
- Shows cost trends and when price increases occurred
- Database: `contract_price_history` (id, contract_id, valid_from, amount, notes)

---

### Cancellation Wizard
- Select contract -> automatically check cancellation deadline
- Letter templates with placeholders (cancellation, information request, custom)
- Output: PDF cancellation letter or email draft
- Step-by-step guided flow

---

### Quick Contract Entry
- Modal with required fields only, no full form
- For fast data capture without leaving the current view

---

### Emergency PDF
- One click: compact PDF with all key contract data for a client
- Useful as an offline backup or for handover

---

### CSV / Excel Export
- Export contract list per client or globally
- Filterable before export (status, category, date range)

---

### Global Search (Ctrl+K)
- Fast overlay search across all fields (title, partner, notes, tags)
- Keyboard-driven navigation

---

### Favourites / Pinned Contracts
- Pin frequently accessed contracts to the top
- Per-user setting

---

### Audit Log
- Track who changed what and when per contract
- Read-only log, stored in `contract_audit_log` table

---

### Calendar / Timeline View
- Calendar view for all contract durations and deadlines
- Horizontal timeline bars per contract, coloured by status

---

### Tauri Desktop Build
- No-Docker distribution for desktop environments
- Single binary, no server setup required

---

## Completed

See [CHANGELOG.md](CHANGELOG.md) for all released features.
