<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function apiResponse(int $code, array $data = [], bool $silent = false): void {
    http_response_code($code);
    if (!$silent && !empty($data)) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
}

function apiAuth(): array {
    $db = db();
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/', $header, $m)) {
        apiResponse(401, [], true);
    }
    $token = trim($m[1]);
    $stmt = $db->prepare("SELECT * FROM api_tokens WHERE token = ? AND active = 1");
    $stmt->execute([$token]);
    $t = $stmt->fetch();
    if (!$t) {
        apiResponse(401, [], true);
    }
    $db->prepare("UPDATE api_tokens SET last_used_at = datetime('now') WHERE id = ?")->execute([$t['id']]);
    $t['permissions'] = json_decode($t['permissions'], true);
    apiRateLimit($t);
    return $t;
}

function apiRateLimit(array $token, int $limit = 60, int $window = 60): void {
    $db = db();
    $now = time();
    $windowStart = $now - ($now % $window);
    $stmt = $db->prepare("INSERT INTO api_rate_limits (token_id, window_start, request_count)
                          VALUES (?, ?, 1)
                          ON CONFLICT(token_id, window_start) DO UPDATE SET request_count = request_count + 1");
    $stmt->execute([$token['id'], $windowStart]);
    $stmt = $db->prepare("SELECT request_count FROM api_rate_limits WHERE token_id = ? AND window_start = ?");
    $stmt->execute([$token['id'], $windowStart]);
    $count = (int) ($stmt->fetchColumn() ?: 0);
    if ($count > $limit) {
        $retryAfter = $window - ($now % $window);
        header('Retry-After: ' . $retryAfter);
        apiResponse(429, ['error' => 'Rate limit exceeded. Try again in ' . $retryAfter . ' seconds.']);
    }
}

function apiCan(array $token, string $permission): void {
    if (!in_array($permission, $token['permissions'])) {
        apiResponse(403, [], true);
    }
}

$db     = db();
$apiUri = substr($uri, 4);
$method = $_SERVER['REQUEST_METHOD'];

// Health-Check (kein Auth erforderlich)
if ($method === 'GET' && ($apiUri === '/health' || $apiUri === '')) {
    apiResponse(200, ['status' => 'ok']);
}

$token  = apiAuth();

if ($apiUri === '/clients' && $method === 'GET') {
    apiCan($token, 'clients.read');
    $where = ['active = 1']; $params = [];
    if ($token['client_id']) { $where[] = 'id = ?'; $params[] = $token['client_id']; }
    $stmt = $db->prepare("SELECT id, name, type, active FROM clients WHERE " . implode(' AND ', $where) . " ORDER BY name");
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    apiResponse(200, ['data' => $clients]);
}

if ($apiUri === '/clients' && $method === 'POST') {
    apiCan($token, 'clients.write');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) apiResponse(400, ['error' => 'Ungültiger JSON-Body.']);
    if (empty($body['name'])) apiResponse(422, ['error' => 'Name fehlt.']);
    $stmt = $db->prepare("INSERT INTO clients (name, type, description) VALUES (?,?,?)");
    $stmt->execute([$body['name'], $body['type'] ?? 'Firma', $body['description'] ?? null]);
    apiResponse(201, ['id' => $db->lastInsertId(), 'message' => 'Mandant angelegt.']);
}

if ($apiUri === '/categories' && $method === 'GET') {
    apiCan($token, 'contracts.read');
    $cats = $db->query("SELECT id, name, color FROM contract_categories ORDER BY name")->fetchAll();
    apiResponse(200, ['data' => $cats]);
}

if ($apiUri === '/contracts' && $method === 'GET') {
    apiCan($token, 'contracts.read');
    $where = []; $params = [];
    if ($token['client_id']) { $where[] = 'c.client_id = ?'; $params[] = $token['client_id']; }
    if (!empty($_GET['client_id'])) { $where[] = 'c.client_id = ?'; $params[] = (int)$_GET['client_id']; }
    if (!empty($_GET['status']))    { $where[] = 'c.status = ?';    $params[] = $_GET['status']; }
    if (!empty($_GET['source']))    { $where[] = 'c.source = ?';    $params[] = $_GET['source']; }
    $sql = "SELECT c.*, cl.name AS client_name, cc.name AS category_name FROM contracts c LEFT JOIN clients cl ON c.client_id = cl.id LEFT JOIN contract_categories cc ON c.category_id = cc.id" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY c.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    apiResponse(200, ['data' => $stmt->fetchAll()]);
}

if (preg_match('#^/contracts/(\d+)$#', $apiUri, $m) && $method === 'GET') {
    apiCan($token, 'contracts.read');
    $where = ['c.id = ?']; $params = [(int)$m[1]];
    if ($token['client_id']) { $where[] = 'c.client_id = ?'; $params[] = $token['client_id']; }
    $stmt = $db->prepare("SELECT c.*, cl.name AS client_name, cc.name AS category_name FROM contracts c LEFT JOIN clients cl ON c.client_id = cl.id LEFT JOIN contract_categories cc ON c.category_id = cc.id WHERE " . implode(' AND ', $where));
    $stmt->execute($params);
    $contract = $stmt->fetch();
    if (!$contract) apiResponse(404, ['error' => 'Vertrag nicht gefunden.']);
    apiResponse(200, ['data' => $contract]);
}

if ($apiUri === '/contracts' && $method === 'POST') {
    apiCan($token, 'contracts.write');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) apiResponse(400, ['error' => 'Ungültiger JSON-Body.']);
    if (empty($body['title']))     apiResponse(422, ['error' => 'Titel fehlt.']);
    if (empty($body['client_id'])) apiResponse(422, ['error' => 'client_id fehlt.']);
    if ($token['client_id']) $body['client_id'] = $token['client_id'];
    // Vertragsnummer generieren
    $cl_stmt = $db->prepare("SELECT name FROM clients WHERE id=?");
    $cl_stmt->execute([$body['client_id']]);
    $cl_row = $cl_stmt->fetch();
    $prefix = strtoupper(substr(preg_replace("/[^a-zA-Z]/", "", $cl_row["name"] ?? "CH"), 0, 4));
    $b36 = base_convert((string)time(), 10, 36);
    $rand = bin2hex(random_bytes(1));
    $appPrefix = strtoupper(substr(preg_replace("/[^a-zA-Z0-9]/", "", APP_NAME), 0, 3));
    $contract_number = $appPrefix . "-" . $prefix . "-" . $b36 . "-" . $rand;
    $stmt = $db->prepare("INSERT INTO contracts (contract_number, client_id, category_id, title, partner, description, start_date, end_date, notice_date, value, billing_interval, status, source, external_id, notes, direction, plan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$contract_number, $body['client_id'], $body['category_id'] ?? null, $body['title'], $body['partner'] ?? null, $body['description'] ?? null, $body['start_date'] ?? null, $body['end_date'] ?? null, $body['notice_date'] ?? null, $body['value'] ?? null, $body['billing_interval'] ?? 'jaehrlich', $body['status'] ?? 'aktiv', $body['source'] ?? 'manuell', $body['external_id'] ?? null, $body['notes'] ?? null, $body['direction'] ?? 'ausgabe', $body['plan'] ?? null]);
    $newId = $db->lastInsertId();
    // Kontakt anlegen wenn mitgegeben
    $contact = $body['contact'] ?? null;
    if (!empty($contact) && is_array($contact)) {
        $cs = $db->prepare("INSERT INTO contract_contacts (contract_id, company, first_name, last_name, email, phone, mobile, street, zip, city, iban, bank, bic) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $cs->execute([
            $newId,
            $contact['company']    ?? null,
            $contact['first_name'] ?? null,
            $contact['last_name']  ?? null,
            $contact['email']      ?? null,
            $contact['phone']      ?? null,
            $contact['mobile']     ?? null,
            $contact['street']     ?? null,
            $contact['zip']        ?? null,
            $contact['city']       ?? null,
            $contact['iban']       ?? null,
            $contact['bank']       ?? null,
            $contact['bic']        ?? null,
        ]);
        // Mail an Kontakt-Email senden
        $contactEmail = $contact['email'] ?? null;
        if ($contactEmail && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            MailService::sendContractCreated($contactEmail, array_merge($body, ['contract_number' => $contract_number]));
        }
    } elseif (!empty($body['email']) && filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
        // Fallback: direktes email-Feld
        MailService::sendContractCreated($body['email'], array_merge($body, ['contract_number' => $contract_number]));
    }
    apiResponse(201, ['id' => $newId, 'contract_number' => $contract_number, 'message' => 'Vertrag angelegt.']);
}

if (preg_match('#^/contracts/(\d+)$#', $apiUri, $m) && $method === 'PUT') {
    apiCan($token, 'contracts.write');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) apiResponse(400, ['error' => 'Ungültiger JSON-Body.']);
    $id = (int)$m[1];
    $where = ['id = ?']; $params = [$id];
    if ($token['client_id']) { $where[] = 'client_id = ?'; $params[] = $token['client_id']; }
    $check = $db->prepare("SELECT id FROM contracts WHERE " . implode(' AND ', $where));
    $check->execute($params);
    if (!$check->fetch()) apiResponse(404, ['error' => 'Vertrag nicht gefunden.']);
    $fields = []; $vals = [];
    foreach (['title','partner','description','start_date','end_date','notice_date','value','billing_interval','status','category_id','notes','plan'] as $f) {
        if (isset($body[$f])) { $fields[] = "$f = ?"; $vals[] = $body[$f]; }
    }
    if (empty($fields)) apiResponse(422, ['error' => 'Keine Felder zum Aktualisieren.']);
    $vals[] = $id;
    $db->prepare("UPDATE contracts SET " . implode(', ', $fields) . " WHERE id = ?")->execute($vals);
    apiResponse(200, ['message' => 'Vertrag aktualisiert.']);
}

if (preg_match('#^/contracts/(\d+)$#', $apiUri, $m) && $method === 'DELETE') {
    apiCan($token, 'contracts.delete');
    $id = (int)$m[1];
    $where = ['id = ?']; $params = [$id];
    if ($token['client_id']) { $where[] = 'client_id = ?'; $params[] = $token['client_id']; }
    $check = $db->prepare("SELECT id FROM contracts WHERE " . implode(' AND ', $where));
    $check->execute($params);
    if (!$check->fetch()) apiResponse(404, ['error' => 'Vertrag nicht gefunden.']);
    $db->prepare("DELETE FROM contracts WHERE id = ?")->execute([$id]);
    apiResponse(200, ['message' => 'Vertrag gelöscht.']);
}

apiResponse(404, ['error' => 'Endpunkt nicht gefunden.']);