<?php
$user = currentUser();
$db   = db();

$action = $_GET['action'] ?? 'index';
$id     = (int)($_GET['id'] ?? 0);
$ids    = allowedClientIds();
$in     = $ids ? implode(',', array_map('intval', $ids)) : '0';

$clientTypes = $db->query("SELECT id, name FROM client_types ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'store';

    if ($action === 'store') {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403); die('Zugriff verweigert.');
        }
        $stmt = $db->prepare("INSERT INTO clients (name, type, description) VALUES (?,?,?)");
        $stmt->execute([$_POST['name'], $_POST['type'], $_POST['description']]);
        $newId = $db->lastInsertId();
        $db->prepare("INSERT INTO user_clients (user_id, client_id) VALUES (?,?)")->execute([$_SESSION['user_id'], $newId]);
        header('Location: /clients');
        exit;
    }

    if ($action === 'update') {
        if (!clientAllowed((int)$_POST['id'])) {
            http_response_code(403); die('Zugriff verweigert.');
        }
        $stmt = $db->prepare("UPDATE clients SET name=?, type=?, description=?, active=? WHERE id=? AND id IN ($in)");
        $stmt->execute([
            $_POST['name'],
            $_POST['type'],
            $_POST['description'],
            isset($_POST['active']) ? 1 : 0,
            $_POST['id'],
        ]);
        header('Location: /clients');
        exit;
    }

    if ($action === 'delete') {
        if (!clientAllowed((int)$_POST['id'])) {
            http_response_code(403); die('Zugriff verweigert.');
        }
        $db->prepare("UPDATE clients SET active=0 WHERE id=? AND id IN ($in)")->execute([$_POST['id']]);
        header('Location: /clients');
        exit;
    }

    if ($action === 'store_type') {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403); die('Zugriff verweigert.');
        }
        $db->prepare("INSERT INTO client_types (name) VALUES (?)")->execute([trim($_POST['type_name'])]);
        header('Location: /clients?action=types');
        exit;
    }

    if ($action === 'delete_type') {
        if ($_SESSION['role'] !== 'admin') {
            http_response_code(403); die('Zugriff verweigert.');
        }
        $db->prepare("DELETE FROM client_types WHERE id=?")->execute([$_POST['type_id']]);
        header('Location: /clients?action=types');
        exit;
    }
}

if ($action === 'create') {
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403); die('Zugriff verweigert.');
    }
    $client = null;
    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/clients/form.php';
} elseif ($action === 'edit' && $id) {
    if (!clientAllowed($id)) {
        http_response_code(403); die('Zugriff verweigert.');
    }
    $stmt = $db->prepare("SELECT * FROM clients WHERE id=? AND id IN ($in)");
    $stmt->execute([$id]);
    $client = $stmt->fetch();
    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/clients/form.php';
} elseif ($action === 'types' && $_SESSION['role'] === 'admin') {
    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/clients/types.php';
} else {
    $clients = $db->query("
        SELECT c.*, COUNT(ct.id) AS contract_count
        FROM clients c
        LEFT JOIN contracts ct ON ct.client_id = c.id
        WHERE c.id IN ($in)
        GROUP BY c.id
        ORDER BY c.name
    ")->fetchAll();
    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/clients/index.php';
}

require __DIR__ . '/../Views/layouts/footer.php';
