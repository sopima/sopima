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

$driver = env('DB_DRIVER', 'mysql');

try {
    if ($driver === 'sqlite') {
        $path = env('DB_SQLITE_PATH', BASE_PATH . '/storage/database/sopima.sqlite');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $pdo = new PDO('sqlite:' . $path);
        $pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (
            filename   TEXT NOT NULL PRIMARY KEY,
            applied_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");
    } else {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', 'db'),
            env('DB_PORT', '3306'),
            env('DB_NAME', 'sopima')
        );
        $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASSWORD'));
        $pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (
            filename   VARCHAR(255) NOT NULL PRIMARY KEY,
            applied_at DATETIME NOT NULL DEFAULT NOW()
        ) ENGINE=InnoDB");
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Fehler: Datenbankverbindung fehlgeschlagen – " . $e->getMessage() . "\n";
    exit(1);
}

$applied = $pdo->query("SELECT filename FROM _migrations ORDER BY filename")
               ->fetchAll(PDO::FETCH_COLUMN, 0);

$dir   = BASE_PATH . '/database/migrations';
$files = glob($dir . '/[0-9]*.sql');
sort($files);

if (empty($files)) {
    echo "Keine Migrationsdateien gefunden.\n";
    exit(0);
}

$countApplied = 0;
$countSkipped = 0;

echo "Sopima Migration Runner\n";
echo "Treiber: " . strtoupper($driver) . "\n";
echo str_repeat('-', 50) . "\n";

foreach ($files as $filepath) {
    $name = basename($filepath);
    if (in_array($name, $applied)) {
        echo "  skip   $name\n";
        $countSkipped++;
        continue;
    }
    $sql = file_get_contents($filepath);
    if (empty(trim($sql))) {
        echo "  empty  $name (uebersprungen)\n";
        continue;
    }
    try {
        $pdo->exec($sql);
        $pdo->prepare("INSERT INTO _migrations (filename) VALUES (?)")->execute([$name]);
        echo "  apply  $name\n";
        $countApplied++;
    } catch (PDOException $e) {
        echo "  ERROR  $name – " . $e->getMessage() . "\n";
        echo "\nAbbruch. Bitte Fehler beheben und erneut ausfuehren.\n";
        exit(1);
    }
}

echo str_repeat('-', 50) . "\n";
echo "Fertig: $countApplied eingespielt, $countSkipped uebersprungen.\n";
