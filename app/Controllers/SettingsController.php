<?php
requireAdmin();
$user   = currentUser();
$db     = db();
$tab    = $_GET["tab"] ?? "users";
$action = $_GET["action"] ?? "index";
$id     = (int)($_GET["id"] ?? 0);

require __DIR__ . "/../Views/layouts/main.php";

if ($tab === "tokens") {
    require __DIR__ . "/TokenController.php";
} elseif ($tab === "general") {
    if ($action === "smtp_test" && $_SERVER["REQUEST_METHOD"] === "POST") {
        $ok = MailService::send(
            SMTP_FROM_EMAIL,
            SMTP_FROM_NAME,
            APP_NAME . ": SMTP-Test",
            "<p>Die SMTP-Konfiguration funktioniert korrekt.</p>"
        );
        header("Location: /settings?tab=general&smtp_test=" . ($ok ? "1" : "0"));
        exit;
    }
    require __DIR__ . "/../Views/settings/general.php";
} elseif ($tab === "mail") {
    if ($action === "save" && $_SERVER["REQUEST_METHOD"] === "POST") {
        $id      = (int)($_POST["id"] ?? 0);
        $subject = trim($_POST["subject"] ?? "");
        $body    = trim($_POST["body"] ?? "");
        $active  = isset($_POST["active"]) ? 1 : 0;
        if ($id && $subject && $body) {
            $stmt = $db->prepare("UPDATE mail_templates SET subject=?, body=?, active=?, updated_at=datetime('now') WHERE id=?");
            $stmt->execute([$subject, $body, $active, $id]);
        }
        header("Location: /settings?tab=mail&saved=1");
        exit;
    }
    require __DIR__ . "/../Views/settings/index.php";
    require __DIR__ . "/../Views/settings/mail_templates.php";
} elseif ($tab === "pdf") {
    $clients = $db->query("SELECT id, name FROM clients WHERE active=1 ORDER BY name")->fetchAll();
    $client_id = (int)($_GET["client_id"] ?? ($clients[0]["id"] ?? 0));
    if ($action === "save" && $_SERVER["REQUEST_METHOD"] === "POST") {
        $client_id = (int)($_POST["client_id"] ?? 0);
        $type      = $_POST["type"] ?? "";
        $title     = trim($_POST["title"] ?? "");
        $body      = trim($_POST["body"] ?? "");
        $attach    = isset($_POST["attach"]) ? 1 : 0;
        $active    = isset($_POST["active"]) ? 1 : 0;
        if ($client_id && $type && $title) {
            $stmt = $db->prepare("INSERT INTO pdf_templates (client_id, type, title, body, attach, active) VALUES (?,?,?,?,?,?)
                ON CONFLICT(client_id, type) DO UPDATE SET title=excluded.title, body=excluded.body, attach=excluded.attach, active=excluded.active, updated_at=datetime('now')");
            $stmt->execute([$client_id, $type, $title, $body, $attach, $active]);
        }
        header("Location: /settings?tab=pdf&client_id=" . $client_id . "&saved=1");
        exit;
    }
    $types = ["datenschutz", "agb", "vertrag"];
    $templates = [];
    foreach ($types as $type) {
        $stmt = $db->prepare("SELECT * FROM pdf_templates WHERE client_id=? AND type=?");
        $stmt->execute([$client_id, $type]);
        $templates[$type] = $stmt->fetch() ?: ["client_id" => $client_id, "type" => $type, "title" => "", "body" => "", "attach" => 0, "active" => 1];
    }
    require __DIR__ . "/../Views/settings/index.php";
    require __DIR__ . "/../Views/settings/pdf_templates.php";
} else {
    $tab = "users";
    require __DIR__ . "/UserController.php";
}

require __DIR__ . "/../Views/layouts/footer.php";
