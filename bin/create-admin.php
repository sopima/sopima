<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

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

echo "Sopima – Admin anlegen\n";
echo "----------------------\n";

echo "Name: ";
$name = trim(fgets(STDIN));

echo "E-Mail: ";
$email = trim(fgets(STDIN));

echo "Passwort: ";
$password = trim(fgets(STDIN));

if (!$name || !$email || !$password) {
    echo "Fehler: Alle Felder sind Pflicht.\n";
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Fehler: Ungültige E-Mail-Adresse.\n";
    exit(1);
}

$driver = env('DB_DRIVER', 'mysql');

if ($driver === 'sqlite') {
    $path = env('DB_SQLITE_PATH', BASE_PATH . '/storage/database/sopima.sqlite');
    $pdo = new PDO('sqlite:' . $path);
} else {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        env('DB_HOST', 'db'),
        env('DB_PORT', '3306'),
        env('DB_NAME', 'sopima')
    );
    $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASSWORD'));
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, active) VALUES (?, ?, ?, 'admin', 1)");
$stmt->execute([$name, $email, $hash]);

echo "Admin '{$name}' erfolgreich angelegt.\n";
