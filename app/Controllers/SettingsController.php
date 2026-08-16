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
    require __DIR__ . "/../Views/settings/general.php";
} else {
    $tab = "users";
    require __DIR__ . "/UserController.php";
}

require __DIR__ . "/../Views/layouts/footer.php";
