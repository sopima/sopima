<?php
requireAdmin();
$user   = currentUser();
$db     = db();
$tab    = $_GET["tab"] ?? "users";
$action = $_GET["action"] ?? "index";
$id     = (int)($_GET["id"] ?? 0);

// Alle POST-Redirects VOR dem HTML-Output
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($tab === "general" && $action === "smtp_test") {
        $ok = MailService::send(
            SMTP_FROM_EMAIL,
            SMTP_FROM_NAME,
            APP_NAME . ": SMTP-Test",
            "<p>Die SMTP-Konfiguration funktioniert korrekt.</p>"
        );
        header("Location: /settings?tab=general&smtp_test=" . ($ok ? "1" : "0"));
        exit;
    }
    if ($tab === "mail" && $action === "save") {
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
    if ($tab === "pdf" && $action === "new") {
        $client_id = (int)($_POST["client_id"] ?? 0);
        if ($client_id && clientAllowed($client_id)) {
            $db->prepare("INSERT INTO pdf_templates (client_id, title, body, attach, active) VALUES (?,?,?,0,1)")
               ->execute([$client_id, __("settings.pdf.untitled"), ""]);
        }
        header("Location: /settings?tab=pdf&client_id=" . $client_id . "&saved=1");
        exit;
    }
    if ($tab === "pdf" && $action === "save") {
        $tpl_id    = (int)($_POST["id"] ?? 0);
        $client_id = (int)($_POST["client_id"] ?? 0);
        $title     = trim($_POST["title"] ?? "");
        $body      = trim($_POST["body"] ?? "");
        $attach    = isset($_POST["attach"]) ? 1 : 0;
        if ($tpl_id && $client_id && clientAllowed($client_id)) {
            $existing = $db->prepare("SELECT file_path FROM pdf_templates WHERE id=? AND client_id=?");
            $existing->execute([$tpl_id, $client_id]);
            $existing = $existing->fetch();
            $file_path = $existing["file_path"] ?? null;

            if (!empty($_FILES["pdf_file"]["tmp_name"])) {
                $dir = "storage/uploads/pdf_templates/" . $client_id . "/";
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $filename = $tpl_id . "_" . time() . ".pdf";
                $dest = $dir . $filename;
                if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $dest)) {
                    if ($file_path && file_exists($file_path)) unlink($file_path);
                    $file_path = $dest;
                }
            }

            $db->prepare("UPDATE pdf_templates SET title=?, body=?, attach=?, file_path=?, updated_at=datetime('now') WHERE id=? AND client_id=?")
               ->execute([$title, $body, $attach, $file_path, $tpl_id, $client_id]);
        }
        header("Location: /settings?tab=pdf&client_id=" . $client_id . "&saved=1");
        exit;
    }
}
if ($tab === "pdf" && $action === "delete_template") {
    $tpl_id    = (int)($_GET["id"] ?? 0);
    $client_id = (int)($_GET["client_id"] ?? 0);
    if ($tpl_id && $client_id && clientAllowed($client_id)) {
        $row = $db->prepare("SELECT file_path FROM pdf_templates WHERE id=? AND client_id=?");
        $row->execute([$tpl_id, $client_id]);
        $row = $row->fetch();
        if ($row && $row["file_path"] && file_exists($row["file_path"])) {
            unlink($row["file_path"]);
        }
        $db->prepare("DELETE FROM pdf_templates WHERE id=? AND client_id=?")->execute([$tpl_id, $client_id]);
    }
    header("Location: /settings?tab=pdf&client_id=" . $client_id . "&saved=1");
    exit;
}
if ($tab === "pdf" && $action === "delete_file") {
    $client_id = (int)($_GET["client_id"] ?? 0);
    $type      = $_GET["type"] ?? "";
    if ($client_id && $type && clientAllowed($client_id)) {
        $row = $db->prepare("SELECT file_path FROM pdf_templates WHERE client_id=? AND type=?");
        $row->execute([$client_id, $type]);
        $row = $row->fetch();
        if ($row && $row["file_path"] && file_exists($row["file_path"])) {
            unlink($row["file_path"]);
        }
        $db->prepare("UPDATE pdf_templates SET file_path=NULL WHERE client_id=? AND type=?")->execute([$client_id, $type]);
    }
    header("Location: /settings?tab=pdf&client_id=" . $client_id . "&saved=1");
    exit;
}

require __DIR__ . "/../Views/layouts/main.php";

if ($tab === "tokens") {
    require __DIR__ . "/TokenController.php";
} elseif ($tab === "general") {
    require __DIR__ . "/../Views/settings/index.php";
    require __DIR__ . "/../Views/settings/general.php";
} elseif ($tab === "mail") {
    require __DIR__ . "/../Views/settings/index.php";
    require __DIR__ . "/../Views/settings/mail_templates.php";
} elseif ($tab === "pdf") {
    $clients = $db->query("SELECT id, name FROM clients WHERE active=1 ORDER BY name")->fetchAll();
    $client_id = (int)($_GET["client_id"] ?? ($clients[0]["id"] ?? 0));
    $stmt = $db->prepare("SELECT * FROM pdf_templates WHERE client_id=? ORDER BY id");
    $stmt->execute([$client_id]);
    $templates = $stmt->fetchAll();
    require __DIR__ . "/../Views/settings/index.php";
    require __DIR__ . "/../Views/settings/pdf_templates.php";
} else {
    $tab = "users";
    require __DIR__ . "/UserController.php";
}

require __DIR__ . "/../Views/layouts/footer.php";