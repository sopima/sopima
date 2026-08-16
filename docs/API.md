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

Permission: clients.read

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

Permission: clients.write

Required fields:
| Field | Type   | Description |
|-------|--------|-------------|
| name  | string | Client name |

Request:
    curl -s -X POST \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"name": "New Client", "type": "Company"}' \
      https://your-domain.example.com/api/clients

Response 201:
    {"id": 3, "message": "Client created."}

---

### GET /api/categories

Returns all contract categories.

Permission: categories.read

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

Permission: contracts.read

Query parameters:
| Parameter  | Type   | Description                                    |
|------------|--------|------------------------------------------------|
| client_id  | int    | Filter by client                               |
| status     | string | aktiv, gekuendigt, abgelaufen, pausiert        |

Request:
    curl -s \
      -H "Authorization: Bearer <token>" \
      "https://your-domain.example.com/api/contracts?client_id=1&status=aktiv"

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
                "billing_interval": "jaehrlich",
                "status": "aktiv",
                "contract_number": "SOP-MEIN-tjkfc7-ae",
                "notes": null,
                "created_at": "2026-08-15 10:00:00",
                "updated_at": "2026-08-15 10:00:00",
                "client_name": "Private",
                "category_name": "Insurance"
            }
        ]
    }

---

### GET /api/contracts/{id}

Returns a single contract.

Permission: contracts.read

Request:
    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/contracts/1

Response 200: (same structure as a single object from GET /api/contracts)

Response 404: (no body)

---

### POST /api/contracts

Creates a new contract.

Permission: contracts.write

Required fields:
| Field     | Type   | Description      |
|-----------|--------|------------------|
| title     | string | Contract title   |
| client_id | int    | Client ID        |

Optional fields:
| Field            | Type   | Default   | Description                                    |
|------------------|--------|-----------|------------------------------------------------|
| partner          | string | null      | Contract partner                               |
| description      | string | null      | Description                                    |
| category_id      | int    | null      | Category ID                                    |
| start_date       | date   | null      | Start date (YYYY-MM-DD)                        |
| end_date         | date   | null      | End date (YYYY-MM-DD)                          |
| notice_date      | date   | null      | Cancellation deadline (YYYY-MM-DD)             |
| value            | float  | null      | Contract value in euros                        |
| billing_interval | string | jaehrlich | einmalig, monatlich, quartalsweise, jaehrlich  |
| status           | string | aktiv     | aktiv, gekuendigt, abgelaufen, pausiert        |
| notes            | string | null      | Internal notes                                 |

Request:
    curl -s -X POST \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"client_id": 1, "title": "Liability Insurance", "partner": "Example AG", "start_date": "2026-01-01", "value": 120, "billing_interval": "jaehrlich", "status": "aktiv"}' \
      https://your-domain.example.com/api/contracts

Response 201:
    {"id": 2, "contract_number": "SOP-MEIN-tjkfc7-ae", "message": "Contract created."}

Response 422:
    {"error": "Title missing."}

Response 400:
    {"error": "Invalid JSON body."}

---

### PUT /api/contracts/{id}

Updates an existing contract (only provided fields are updated).

Permission: contracts.write

Request:
    curl -s -X PUT \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"status": "gekuendigt", "notice_date": "2026-12-31"}' \
      https://your-domain.example.com/api/contracts/2

Response 200:
    {"message": "Contract updated."}

Response 404: (no body)

---

### DELETE /api/contracts/{id}

Deletes a contract.

Permission: contracts.delete

Request:
    curl -s -X DELETE \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/contracts/2

Response 200:
    {"message": "Contract deleted."}

Response 404: (no body)

---

## Client-bound tokens

If a token is bound to a specific client, `client_id` is automatically set to that client for all requests — regardless of what is specified in the request body or query parameters.

---

## Error reference

| HTTP code | Meaning                          |
|-----------|----------------------------------|
| 200       | Success                          |
| 201       | Resource created                 |
| 400       | Invalid request (no JSON)        |
| 401       | Unauthenticated (no body)        |
| 403       | No permission (no body)          |
| 404       | Not found                        |
| 422       | Required field missing           |
| 429       | Rate limit exceeded              |

---

## Rate limiting

60 requests per 60 seconds per token. When exceeded:

    HTTP 429
    Retry-After: 60

---

## Versioning

The API is currently at version v1 (implicit).
Explicit versioning (/api/v1/) will be introduced for breaking changes.