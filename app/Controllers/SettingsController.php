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
} else {
    $tab = "users";
    require __DIR__ . "/UserController.php";
}

require __DIR__ . "/../Views/layouts/footer.php";
