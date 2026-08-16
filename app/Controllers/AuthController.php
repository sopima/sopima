<?php
$db    = db();
$error = '';

// CSRF-Token generieren (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. CSRF prüfen
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $error = __('auth.invalid_request');
        require __DIR__ . '/../Views/layouts/login.php';
        exit;
    }
    // CSRF-Token nach Prüfung erneuern
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // 2. Honeypot prüfen – Bots füllen das versteckte Feld aus
    if (!empty($_POST['website'])) {
        // Stillschweigend abbrechen – kein Hinweis an den Bot
        require __DIR__ . '/../Views/layouts/login.php';
        exit;
    }

    // 3. Brute-Force: IP ermitteln
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ip = trim(explode(',', $ip)[0]);

    // Alte Einträge bereinigen (älter als 15 Minuten)
    $db->prepare("DELETE FROM login_attempts WHERE attempted_at < datetime('now', '-15 minutes')")
       ->execute();

    // Fehlversuche der letzten 15 Minuten zählen
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip=? AND attempted_at > datetime('now', '-15 minutes')");
    $stmt->execute([$ip]);
    $attempts = (int)$stmt->fetchColumn();

    if ($attempts >= 5) {
        $error = __('auth.too_many');
        require __DIR__ . '/../Views/layouts/login.php';
        exit;
    }

    // 4. Eingaben bereinigen
    $email    = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = __('auth.empty_fields');
        require __DIR__ . '/../Views/layouts/login.php';
        exit;
    }

    // 5. User laden
    $stmt = $db->prepare("SELECT id, name, role, password_hash FROM users WHERE email=? AND active=1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Erfolg: Fehlversuche dieser IP löschen
        $db->prepare("DELETE FROM login_attempts WHERE ip=?")->execute([$ip]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        unset($_SESSION['csrf_token']);
        header('Location: /dashboard');
        exit;
    }

    // Fehlversuch protokollieren
    $db->prepare("INSERT INTO login_attempts (ip, attempted_at) VALUES (?, datetime('now'))")->execute([$ip]);
    $error = __('auth.wrong');
}

// CSRF-Token sicherstellen (nach POST-Fehler)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/../Views/layouts/login.php';