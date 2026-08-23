# Sopima API – Documentation

## Overview

The Sopima API allows external systems to access contracts, clients and categories. The API follows REST principles and communicates exclusively via JSON.

Base URL: `https://your-domain.example.com/api`

---

## Authentication

All API requests (except `/api/health`) require a valid Bearer token in the Authorization header:

    Authorization: Bearer <token>

Tokens are managed in Sopima under Settings > API Tokens.

### Permissions

| Permission        | Description              |
|-------------------|--------------------------|
| contracts.read    | Read contracts           |
| contracts.write   | Create/update contracts  |
| contracts.delete  | Delete contracts         |
| clients.read      | Read clients             |
| clients.write     | Create clients           |
| categories.read   | Read categories          |

### Authentication errors

Auth and permission errors intentionally return no body:

    HTTP 401  (no token or invalid token)
    HTTP 403  (missing permission)

---

## Endpoints

---

### GET /api/health

Health check for uptime monitoring. No authentication required.

Request:

    curl -s https://your-domain.example.com/api/health

Response 200:

    {"status": "ok"}

---

### GET /api/clients

Returns all active clients.

Permission: `clients.read`

Request:

    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/clients

Response 200:

    {
        "data": [
            {"id": 1, "name": "Private",    "type": "Private", "active": 1},
            {"id": 2, "name": "My Company", "type": "Company", "active": 1}
        ]
    }

---

### POST /api/clients

Creates a new client.

Permission: `clients.write`

| Field | Type   | Required | Description |
|-------|--------|----------|-------------|
| name  | string | yes      | Client name |
| type  | string | no       | Client type (e.g. "Private", "Company") |

Request:

    curl -s -X POST \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"name": "New Client", "type": "Company"}' \
      https://your-domain.example.com/api/clients

Response 201:

    {"id": 3, "message": "Client created."}

Response 422:

    {"error": "Name missing."}

---

### GET /api/categories

Returns all contract categories.

Permission: `categories.read`

Request:

    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/categories

Response 200:

    {
        "data": [
            {"id": 1, "name": "Insurance", "color": "#3b82f6"},
            {"id": 2, "name": "Rent",      "color": "#10b981"}
        ]
    }

---

### GET /api/contracts

Returns contracts. Supports filtering via query parameters.

Permission: `contracts.read`

Query parameters:

| Parameter  | Type   | Description                        |
|------------|--------|------------------------------------|
| client_id  | int    | Filter by client                   |
| status     | string | active, cancelled, expired, paused |

Request:

    curl -s \
      -H "Authorization: Bearer <token>" \
      "https://your-domain.example.com/api/contracts?client_id=1&status=active"

Response 200:

    {
        "data": [
            {
                "id": 1,
                "client_id": 1,
                "category_id": 1,
                "title": "Liability Insurance",
                "partner": "Example Insurance AG",
                "description": null,
                "start_date": "2026-01-01",
                "end_date": "2027-01-01",
                "notice_date": "2026-10-01",
                "value": "120.00",
                "billing_interval": "yearly",
                "direction": "expense",
                "status": "active",
                "contract_number": "SOP-0001-abc123",
                "external_id": null,
                "source": "manual",
                "notes": null,
                "plan": null,
                "created_at": "2026-01-01 10:00:00",
                "updated_at": "2026-01-01 10:00:00",
                "client_name": "Private",
                "category_name": "Insurance"
            }
        ]
    }

---

### GET /api/contracts/{id}

Returns a single contract by ID.

Permission: `contracts.read`

Request:

    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/contracts/1

Response 200:

    {
        "id": 1,
        "client_id": 1,
        "category_id": 1,
        "title": "Liability Insurance",
        "partner": "Example Insurance AG",
        "description": null,
        "start_date": "2026-01-01",
        "end_date": "2027-01-01",
        "notice_date": "2026-10-01",
        "value": "120.00",
        "billing_interval": "yearly",
        "direction": "expense",
        "status": "active",
        "contract_number": "SOP-0001-abc123",
        "external_id": null,
        "source": "manual",
        "notes": null,
        "plan": null,
        "created_at": "2026-01-01 10:00:00",
        "updated_at": "2026-01-01 10:00:00",
        "client_name": "Private",
        "category_name": "Insurance"
    }

Response 404: (no body)

---

### POST /api/contracts

Creates a new contract. Optionally includes a contact person and triggers a mail notification.

Permission: `contracts.write`

Required fields:

| Field     | Type   | Description    |
|-----------|--------|----------------|
| title     | string | Contract title |
| client_id | int    | Client ID      |

Optional fields:

| Field            | Type   | Default  | Description                              |
|------------------|--------|----------|------------------------------------------|
| partner          | string | null     | Contract partner name                    |
| description      | string | null     | Description                              |
| category_id      | int    | null     | Category ID                              |
| start_date       | date   | null     | Start date (YYYY-MM-DD)                  |
| end_date         | date   | null     | End date (YYYY-MM-DD)                    |
| notice_date      | date   | null     | Cancellation deadline (YYYY-MM-DD)       |
| value            | float  | null     | Contract value in euros                  |
| billing_interval | string | yearly   | yearly, monthly, quarterly, biannual     |
| direction        | string | expense  | expense, income                          |
| status           | string | active   | active, cancelled, expired, paused       |
| notes            | string | null     | Internal notes                           |
| external_id      | string | null     | External reference ID                    |
| source           | string | manual   | Origin of the contract (e.g. api)        |
| plan             | string | null     | Plan or tier information                 |
| email            | string | null     | Fallback email for mail notification     |

Contact person (optional, nested object):

| Field                  | Type   | Description          |
|------------------------|--------|----------------------|
| contact.company        | string | Company name         |
| contact.first_name     | string | First name           |
| contact.last_name      | string | Last name            |
| contact.email          | string | Email (used for mail notification if set) |
| contact.phone          | string | Phone number         |
| contact.mobile         | string | Mobile number        |
| contact.street         | string | Street address       |
| contact.zip            | string | ZIP code             |
| contact.city           | string | City                 |
| contact.iban           | string | IBAN                 |
| contact.bank           | string | Bank name            |
| contact.bic            | string | BIC/SWIFT            |

Request:

    curl -s -X POST \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{
        "client_id": 1,
        "title": "Liability Insurance",
        "partner": "Example AG",
        "start_date": "2026-01-01",
        "end_date": "2027-01-01",
        "notice_date": "2026-10-01",
        "value": 120,
        "billing_interval": "yearly",
        "direction": "expense",
        "status": "active",
        "contact": {
          "first_name": "Jane",
          "last_name": "Doe",
          "email": "jane.doe@example.com"
        }
      }' \
      https://your-domain.example.com/api/contracts

Response 201:

    {"id": 2, "contract_number": "SOP-0001-abc123", "message": "Contract created."}

Response 422:

    {"error": "Title missing."}

Response 400:

    {"error": "Invalid JSON body."}

---

### PUT /api/contracts/{id}

Updates an existing contract. Only provided fields are updated.

Permission: `contracts.write`

Updatable fields:

| Field            | Type   | Description                              |
|------------------|--------|------------------------------------------|
| title            | string | Contract title                           |
| partner          | string | Contract partner name                    |
| description      | string | Description                              |
| category_id      | int    | Category ID                              |
| start_date       | date   | Start date (YYYY-MM-DD)                  |
| end_date         | date   | End date (YYYY-MM-DD)                    |
| notice_date      | date   | Cancellation deadline (YYYY-MM-DD)       |
| value            | float  | Contract value in euros                  |
| billing_interval | string | yearly, monthly, quarterly, biannual     |
| direction        | string | expense, income                          |
| status           | string | active, cancelled, expired, paused       |
| notes            | string | Internal notes                           |
| plan             | string | Plan or tier information                 |

Request:

    curl -s -X PUT \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"status": "cancelled", "notice_date": "2026-12-31"}' \
      https://your-domain.example.com/api/contracts/2

Response 200:

    {"message": "Contract updated."}

Response 404: (no body)

Response 422:

    {"error": "No fields to update."}

---

### DELETE /api/contracts/{id}

Deletes a contract permanently.

Permission: `contracts.delete`

Request:

    curl -s -X DELETE \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/contracts/2

Response 200:

    {"message": "Contract deleted."}

Response 404: (no body)

---

## Mail notifications

When a contract is created (`POST /api/contracts`), a mail notification is sent automatically if:

- `contact.email` is set and valid, **or**
- `email` (top-level field) is set and valid

The mail template used is the one configured for the `contract.created` event under Settings > Mail Templates. The template must be active for the mail to be sent.

---

## Client-bound tokens

If a token is bound to a specific client, `client_id` is automatically set to that client for all requests — regardless of what is passed in the request body or query parameters.

---

## Error reference

| HTTP code | Meaning                          |
|-----------|----------------------------------|
| 200       | Success                          |
| 201       | Resource created                 |
| 400       | Invalid request (malformed JSON) |
| 401       | Unauthenticated (no body)        |
| 403       | No permission (no body)          |
| 404       | Not found (no body)              |
| 422       | Required field missing           |
| 429       | Rate limit exceeded              |
| 500       | Internal server error            |

---

## Rate limiting

60 requests per 60 seconds per token. When exceeded:

    HTTP 429
    Retry-After: 60

---

## Versioning

The API is currently at version v1 (implicit).
Explicit versioning (`/api/v1/`) will be introduced for breaking changes.