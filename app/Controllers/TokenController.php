<?php
requireAdmin();
$user = currentUser();
$db   = db();

$action = $_GET['action'] ?? 'index';
$id     = (int)($_GET['id'] ?? 0);

$allClients = $db->query("SELECT id, name FROM clients WHERE active=1 ORDER BY name")->fetchAll();
$allPerms   = ['contracts.read', 'contracts.write', 'contracts.delete', 'clients.read', 'clients.write', 'categories.read'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'store';

    if ($action === 'store') {
        $token = bin2hex(random_bytes(32));
        $perms = json_encode($_POST['permissions'] ?? []);
        $stmt  = $db->prepare("INSERT INTO api_tokens (name, token, client_id, permissions) VALUES (?,?,?,?)");
        $stmt->execute([
            $_POST['name'],
            $token,
            $_POST['client_id'] ?: null,
            $perms,
        ]);
        $newId = $db->lastInsertId();
        header('Location: /settings?tab=tokens&action=show&id=' . $newId . '&token=' . $token);
        exit;
    }

    if ($action === 'toggle') {
        $db->prepare("UPDATE api_tokens SET active = NOT active WHERE id=?")->execute([$_POST['id']]);
        header('Location: /settings?tab=tokens');
        exit;
    }

    if ($action === 'delete') {
        $db->prepare("DELETE FROM api_tokens WHERE id=?")->execute([$_POST['id']]);
        header('Location: /settings?tab=tokens');
        exit;
    }
}

if ($action === 'create') {
    require __DIR__ . '/../Views/tokens/form.php';
} elseif ($action === 'show' && $id) {
    $stmt = $db->prepare("SELECT t.*, c.name AS client_name FROM api_tokens t LEFT JOIN clients c ON c.id = t.client_id WHERE t.id=?");
    $stmt->execute([$id]);
    $apiToken    = $stmt->fetch();
    $plainToken  = $_GET['token'] ?? null;
    require __DIR__ . '/../Views/tokens/show.php';
} else {
    $tokens = $db->query("
        SELECT t.*, c.name AS client_name
        FROM api_tokens t
        LEFT JOIN clients c ON c.id = t.client_id
        ORDER BY t.created_at DESC
    ")->fetchAll();
    require __DIR__ . '/../Views/settings/index.php';
    require __DIR__ . '/../Views/tokens/index.php';
}


