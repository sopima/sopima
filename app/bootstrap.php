<?php
declare(strict_types=1);
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
$dotenv = BASE_PATH . '/.env';
if (file_exists($dotenv)) {
    foreach (file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed { return $_ENV[$key] ?? $default; }
}
if (!defined('APP_NAME'))        define('APP_NAME',        env('APP_NAME', 'Sopima'));
if (!defined('APP_URL'))         define('APP_URL',         env('APP_URL', ''));
if (!defined('SMTP_HOST'))       define('SMTP_HOST',       env('MAIL_HOST', ''));
if (!defined('SMTP_PORT'))       define('SMTP_PORT',       (int)env('MAIL_PORT', 587));
if (!defined('SMTP_USERNAME'))   define('SMTP_USERNAME',   env('MAIL_USERNAME', ''));
if (!defined('SMTP_PASSWORD'))   define('SMTP_PASSWORD',   env('MAIL_PASSWORD', ''));
if (!defined('SMTP_ENCRYPTION')) define('SMTP_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls'));
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', env('MAIL_FROM', ''));
if (!defined('SMTP_FROM_NAME'))  define('SMTP_FROM_NAME',  env('MAIL_FROM_NAME', env('APP_NAME', 'Sopima')));
if (!defined('APP_LOCALE'))      define('APP_LOCALE',      env('APP_LOCALE', 'de'));

require_once BASE_PATH . '/app/Helpers/db.php';
require_once BASE_PATH . '/app/Helpers/i18n.php';
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/Services/MailService.php';