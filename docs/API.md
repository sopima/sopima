# Sopima API – Dokumentation

## Übersicht

Die Sopima API ermöglicht externen Systemen den Zugriff auf Verträge, Mandanten und Kategorien. Die API folgt REST-Prinzipien und kommuniziert ausschließlich über JSON.

Base URL: `https://your-domain.example.com/api`

---

## Authentifizierung

Alle API-Anfragen (außer `/api/health`) erfordern einen gültigen Bearer-Token im Authorization-Header:

    Authorization: Bearer <token>

Tokens werden in Sopima unter Einstellungen > API-Tokens verwaltet.

### Berechtigungen

| Permission        | Beschreibung           |
|-------------------|------------------------|
| contracts.read    | Verträge abrufen       |
| contracts.write   | Verträge anlegen       |
| clients.read      | Mandanten abrufen      |
| categories.read   | Kategorien abrufen     |

### Fehler-Antworten Authentifizierung

Auth- und Berechtigungsfehler geben bewusst keinen Body zurück:

    HTTP 401  (kein Token oder ungültiger Token)
    HTTP 403  (fehlende Berechtigung)

---

## Endpunkte

---

### GET /api/health

Health-Check für Uptime-Monitoring. Keine Authentifizierung erforderlich.

Request:
    curl -s https://your-domain.example.com/api/health

Response 200:
    {"status": "ok"}

---

### GET /api/clients

Gibt alle aktiven Mandanten zurück.

Berechtigung: clients.read

Request:
    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/clients

Response 200:
    {
        "data": [
            {"id": 1, "name": "Privat",      "type": "Privat",  "active": 1},
            {"id": 2, "name": "Meine Firma", "type": "Firma",   "active": 1}
        ]
    }

---

### POST /api/clients

Legt einen neuen Mandanten an.

Berechtigung: clients.write

Pflichtfelder:
| Feld | Typ    | Beschreibung   |
|------|--------|----------------|
| name | string | Mandantenname  |

Request:
    curl -s -X POST \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"name": "Neuer Mandant", "type": "Firma"}' \
      https://your-domain.example.com/api/clients

Response 201:
    {"id": 3, "message": "Mandant angelegt."}

---

### GET /api/categories

Gibt alle Vertragskategorien zurück.

Berechtigung: categories.read

Request:
    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/categories

Response 200:
    {
        "data": [
            {"id": 1, "name": "Versicherung", "color": "#3b82f6"},
            {"id": 2, "name": "Miete",        "color": "#10b981"}
        ]
    }

---

### GET /api/contracts

Gibt Verträge zurück. Unterstützt Filter per Query-Parameter.

Berechtigung: contracts.read

Query-Parameter:
| Parameter  | Typ    | Beschreibung                              |
|------------|--------|-------------------------------------------|
| client_id  | int    | Filter auf einen Mandanten                |
| status     | string | aktiv, gekuendigt, abgelaufen, pausiert   |

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
                "title": "Haftpflichtversicherung",
                "partner": "Musterversicherung AG",
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
                "client_name": "Privat",
                "category_name": "Versicherung"
            }
        ]
    }

---

### GET /api/contracts/{id}

Gibt einen einzelnen Vertrag zurück.

Berechtigung: contracts.read

Request:
    curl -s \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/contracts/1

Response 200: (gleiche Struktur wie einzelnes Objekt aus GET /api/contracts)

Response 404: (kein Body)

---

### POST /api/contracts

Legt einen neuen Vertrag an.

Berechtigung: contracts.write

Pflichtfelder:
| Feld      | Typ    | Beschreibung        |
|-----------|--------|---------------------|
| title     | string | Vertragsbezeichnung |
| client_id | int    | ID des Mandanten    |

Optionale Felder:
| Feld             | Typ    | Standard  | Beschreibung                                  |
|------------------|--------|-----------|-----------------------------------------------|
| partner          | string | null      | Vertragspartner                               |
| description      | string | null      | Beschreibung                                  |
| category_id      | int    | null      | Kategorie-ID                                  |
| start_date       | date   | null      | Startdatum (YYYY-MM-DD)                       |
| end_date         | date   | null      | Enddatum (YYYY-MM-DD)                         |
| notice_date      | date   | null      | Kündigungsfrist (YYYY-MM-DD)                  |
| value            | float  | null      | Vertragswert in Euro                          |
| billing_interval | string | jaehrlich | einmalig, monatlich, quartalsweise, jaehrlich |
| status           | string | aktiv     | aktiv, gekuendigt, abgelaufen, pausiert       |
| notes            | string | null      | Interne Notizen                               |

Request:
    curl -s -X POST \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"client_id": 1, "title": "Haftpflichtversicherung", "partner": "Musterversicherung AG", "start_date": "2026-01-01", "value": 120, "billing_interval": "jaehrlich", "status": "aktiv"}' \
      https://your-domain.example.com/api/contracts

Response 201:
    {"id": 2, "contract_number": "SOP-MEIN-tjkfc7-ae", "message": "Vertrag angelegt."}

Response 422:
    {"error": "Titel fehlt."}

Response 400:
    {"error": "Ungültiger JSON-Body."}

---

### PUT /api/contracts/{id}

Aktualisiert einen bestehenden Vertrag (nur übergebene Felder werden aktualisiert).

Berechtigung: contracts.write

Request:
    curl -s -X PUT \
      -H "Authorization: Bearer <token>" \
      -H "Content-Type: application/json" \
      -d '{"status": "gekuendigt", "notice_date": "2026-12-31"}' \
      https://your-domain.example.com/api/contracts/2

Response 200:
    {"message": "Vertrag aktualisiert."}

Response 404: (kein Body)

---

### DELETE /api/contracts/{id}

Löscht einen Vertrag.

Berechtigung: contracts.write

Request:
    curl -s -X DELETE \
      -H "Authorization: Bearer <token>" \
      https://your-domain.example.com/api/contracts/2

Response 200:
    {"message": "Vertrag gelöscht."}

Response 404: (kein Body)

---

## Mandanten-gebundene Tokens

Wenn ein Token an einen bestimmten Mandanten gebunden ist, wird client_id bei allen
Requests automatisch auf diesen Mandanten gesetzt — unabhängig davon was im
Request-Body oder Query-Parameter angegeben wird.

---

## Fehler-Referenz

| HTTP-Code | Bedeutung                        |
|-----------|----------------------------------|
| 200       | Erfolg                           |
| 201       | Ressource angelegt               |
| 400       | Ungültiger Request (kein JSON)   |
| 401       | Nicht authentifiziert (kein Body)|
| 403       | Keine Berechtigung (kein Body)   |
| 404       | Nicht gefunden                   |
| 422       | Pflichtfeld fehlt                |
| 429       | Rate Limit erreicht              |

---

## Rate Limiting

60 Requests pro 60 Sekunden pro Token. Bei Überschreitung:

    HTTP 429
    Retry-After: 60

---

## Versionierung

Die API ist aktuell in Version v1 (implicit).
Eine explizite Versionierung (/api/v1/) wird bei Breaking Changes eingeführt.
