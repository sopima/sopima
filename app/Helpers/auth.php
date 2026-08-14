<?php
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Zugriff verweigert.');
    }
}

function allowedClientIds(): array {
    static $ids = null;
    if ($ids === null) {
        $stmt = db()->prepare('SELECT client_id FROM user_clients WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    return $ids;
}

function clientAllowed(int $clientId): bool {
    return in_array($clientId, allowedClientIds());
}
