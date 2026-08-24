<?php

declare(strict_types=1);



define('BASE_PATH', dirname(__DIR__));

$dotenv = BASE_PATH . '/.env';
if (file_exists($dotenv)) {
    foreach (file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}

if (env('APP_DEBUG', 'false') === 'true') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

define('APP_NAME', env('APP_NAME', 'Sopima'));
define('APP_URL',         env('APP_URL', ''));
define('SMTP_HOST',       env('MAIL_HOST', ''));
define('SMTP_PORT',       (int)env('MAIL_PORT', 587));
define('SMTP_USERNAME',   env('MAIL_USERNAME', ''));
define('SMTP_PASSWORD',   env('MAIL_PASSWORD', ''));
define('SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
define('SMTP_FROM_EMAIL', env('MAIL_FROM', ''));
define('SMTP_FROM_NAME',  env('MAIL_FROM_NAME', env('APP_NAME', 'Sopima')));

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/Services/MailService.php';
require_once BASE_PATH . '/app/Helpers/db.php';
require_once BASE_PATH . '/app/Helpers/auth.php';
require_once BASE_PATH . '/app/Helpers/i18n.php';
load_lang(env('APP_LOCALE', 'de'));

ini_set("session.gc_maxlifetime", 86400);
session_set_cookie_params(["lifetime" => 86400, "path" => "/", "secure" => false, "httponly" => true, "samesite" => "Lax"]);
session_start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    ''           => ['auth' => true,  'admin' => false, 'controller' => 'DashboardController'],
    '/dashboard' => ['auth' => true,  'admin' => false, 'controller' => 'DashboardController'],
    '/contracts' => ['auth' => true,  'admin' => false, 'controller' => 'ContractController'],
    '/clients'   => ['auth' => true,  'admin' => false, 'controller' => 'ClientController'],
    '/users'     => ['auth' => true,  'admin' => true,  'controller' => 'UserController'],
    '/tokens'        => ['auth' => true,  'admin' => true,  'controller' => 'TokenController'],
    '/settings'      => ['auth' => true,  'admin' => true,  'controller' => 'SettingsController'],
    '/backup'        => ['auth' => true,  'admin' => true,  'controller' => 'BackupController'],
    '/notifications' => ['auth' => true,  'admin' => false, 'controller' => 'NotificationController'],
    '/login'     => ['auth' => false, 'admin' => false, 'controller' => 'AuthController'],
    '/logout'    => ['auth' => false, 'admin' => false, 'controller' => null],
];

function middleware(array $route): void {
    if ($route['auth'] && !isLoggedIn()) {
        header('Location: /login');
        exit;
    }
    if ($route['admin'] && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        require BASE_PATH . '/app/views/layouts/main.php';
        echo '<div style="padding:3rem;text-align:center;color:var(--text-muted);">
            <i class="ti ti-lock" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.4;"></i>
            <h3 style="color:rgba(255,255,255,.6);margin-bottom:.5rem;">Zugriff verweigert</h3>
            <p>Diese Seite ist nur für Administratoren.</p>
            <a href="/dashboard" class="btn btn-outline" style="margin-top:1.5rem;">← Dashboard</a>
        </div>';
        require BASE_PATH . '/app/views/layouts/footer.php';
        exit;
    }
}

// Setup-Wizard – vor DB und Auth prüfen
if ($uri === '/setup') {
    require BASE_PATH . '/app/Controllers/SetupController.php';
    exit;
}

if (str_starts_with($uri, '/api')) {
    require BASE_PATH . '/app/Controllers/ApiController.php';
    exit;
}

// Kein Admin vorhanden → Setup
if ($uri !== '/setup') {
    try {
        $count = db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ((int)$count === 0) {
            header('Location: /setup');
            exit;
        }
    } catch (Throwable) {
        header('Location: /setup');
        exit;
    }
}

if ($uri === '/logout') {
    session_destroy();
    header('Location: /login');
    exit;
}

// Letter-Routen: /contracts/{id}/letter und /contracts/{id}/letter/{tid}/pdf
if (preg_match('#^/contracts/(\d+)/letter$#', $uri, $m)) {
    middleware(['auth' => true, 'admin' => false]);
    require BASE_PATH . '/app/Controllers/LetterController.php';
    $ctrl = new Sopima\Controllers\LetterController(db());
    $ctrl->selectTemplate((int)$m[1]);
    exit;
}
if (preg_match('#^/contracts/(\d+)/letter/(\d+)/preview$#', $uri, $m)) {
    middleware(['auth' => true, 'admin' => false]);
    require BASE_PATH . '/app/Controllers/LetterController.php';
    $ctrl = new Sopima\Controllers\LetterController(db());
    $ctrl->previewPdf((int)$m[1], (int)$m[2]);
    exit;
}
if (preg_match('#^/contracts/(\d+)/letter/(\d+)/pdf$#', $uri, $m)) {
    middleware(['auth' => true, 'admin' => false]);
    require BASE_PATH . '/app/Controllers/LetterController.php';
    $ctrl = new Sopima\Controllers\LetterController(db());
    $ctrl->downloadPdf((int)$m[1], (int)$m[2]);
    exit;
}
// Settings: Briefvorlagen
if (preg_match('#^/settings/letter-templates(/(\d+))?$#', $uri, $m)) {
    middleware(['auth' => true, 'admin' => true]);
    require BASE_PATH . '/app/Controllers/LetterController.php';
    $ctrl = new Sopima\Controllers\LetterController(db());
    $id = isset($m[2]) ? (int)$m[2] : null;
    if ($method === 'POST' && $id && isset($_POST['_delete'])) {
        $ctrl->settingsDelete($id);
    } elseif ($method === 'POST' && $id) {
        $ctrl->settingsUpdate($id);
    } elseif ($method === 'POST') {
        $ctrl->settingsCreate();
    } elseif ($id) {
        $ctrl->settingsEdit($id);
    } else {
        $ctrl->settingsIndex();
    }
    exit;
}

if (isset($routes[$uri])) {
    $route = $routes[$uri];
    middleware($route);
    if ($route['controller']) {
        require BASE_PATH . '/app/Controllers/' . $route['controller'] . '.php';
    }
} else {
    http_response_code(404);
    if (isLoggedIn()) {
        $user = currentUser();
        require BASE_PATH . '/app/views/layouts/main.php';
        echo '<div style="padding:3rem;text-align:center;color:var(--text-muted);">
            <i class="ti ti-error-404" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.4;"></i>
            <h3 style="color:rgba(255,255,255,.6);margin-bottom:.5rem;">Seite nicht gefunden</h3>
            <a href="/dashboard" class="btn btn-outline" style="margin-top:1.5rem;">← Dashboard</a>
        </div>';
        require BASE_PATH . '/app/views/layouts/footer.php';
    } else {
        header('Location: /login');
    }
}
