<?php

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $driver = env('DB_DRIVER', 'mysql');
        try {
            if ($driver === 'sqlite') {
                $path = env('DB_SQLITE_PATH', BASE_PATH . '/storage/database/sopima.sqlite');
                $pdo = new PDO('sqlite:' . $path, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } else {
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    env('DB_HOST', 'db'),
                    env('DB_PORT', '3306'),
                    env('DB_NAME', 'sopima')
                );
                $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASSWORD'), [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            }
        } catch (PDOException $e) {
            die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
        }
    }
    return $pdo;
}
