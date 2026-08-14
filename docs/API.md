# Sopima API

Basis-URL: `/api/`

## Authentifizierung

Bearer Token im Header:
Authorization: Bearer <token>
## Endpunkte

### GET /api/health
Kein Auth erforderlich. Gibt den Status der Anwendung zurück.

**Response 200:**
```json
{"status": "ok"}
```

---

## Fehlerformat

Alle Fehler werden einheitlich zurückgegeben:

```json
{"error": "Beschreibung des Fehlers"}
```

## HTTP-Statuscodes

| Code | Bedeutung |
|------|-----------|
| 200  | OK |
| 201  | Erstellt |
| 400  | Ungültige Anfrage |
| 401  | Nicht authentifiziert |
| 403  | Kein Zugriff |
| 404  | Nicht gefunden |
| 429  | Rate Limit überschritten |
| 500  | Serverfehler |

## Rate Limiting

60 Requests pro 60 Sekunden pro Token. Bei Überschreitung: HTTP 429 mit Header `Retry-After`.
