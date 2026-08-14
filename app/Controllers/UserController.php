<?php
requireAdmin();
$user = currentUser();
$db   = db();

$action = $_GET['action'] ?? 'index';
$id     = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'store';

    if ($action === 'store') {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)");
        $stmt->execute([$_POST['name'], $_POST['email'], $hash, $_POST['role']]);
        $newId = $db->lastInsertId();
        // Mandanten zuweisen
        if (!empty($_POST['clients'])) {
            foreach ($_POST['clients'] as $cid) {
                $db->prepare("INSERT IGNORE INTO user_clients (user_id, client_id) VALUES (?,?)")->execute([$newId, $cid]);
            }
        }
        header('Location: /settings?tab=users');
        exit;
    }

    if ($action === 'update') {
        $stmt = $db->prepare("UPDATE users SET name=?, email=?, role=?, active=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['role'],
            isset($_POST['active']) ? 1 : 0,
            $_POST['id'],
        ]);
        if (!empty($_POST['password'])) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $_POST['id']]);
        }
        // Mandanten neu setzen
        $db->prepare("DELETE FROM user_clients WHERE user_id=?")->execute([$_POST['id']]);
        if (!empty($_POST['clients'])) {
            foreach ($_POST['clients'] as $cid) {
                $db->prepare("INSERT IGNORE INTO user_clients (user_id, client_id) VALUES (?,?)")->execute([$_POST['id'], $cid]);
            }
        }
        header('Location: /settings?tab=users');
        exit;
    }

    if ($action === 'delete') {
        if ($_POST['id'] != $_SESSION['user_id']) {
            $db->prepare("DELETE FROM users WHERE id=?")->execute([$_POST['id']]);
        }
        header('Location: /settings?tab=users');
        exit;
    }
}

$allClients = $db->query("SELECT id, name, type FROM clients WHERE active=1 ORDER BY name")->fetchAll();

if ($action === 'create') {
    $editUser     = null;
    $userClients  = [];
    require __DIR__ . '/../Views/users/form.php';
} elseif ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT id, name, email, role, active FROM users WHERE id=?");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch();
    $stmt2 = $db->prepare("SELECT client_id FROM user_clients WHERE user_id=?");
    $stmt2->execute([$id]);
    $userClients = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    require __DIR__ . '/../Views/users/form.php';
} else {
    $users = $db->query("
        SELECT u.*, GROUP_CONCAT(c.name, ', ') AS client_names
        FROM users u
        LEFT JOIN user_clients uc ON uc.user_id = u.id
        LEFT JOIN clients c ON c.id = uc.client_id
        GROUP BY u.id
        ORDER BY u.name
    ")->fetchAll();
    require __DIR__ . '/../Views/settings/index.php';
    require __DIR__ . '/../Views/users/index.php';
}


