<?php

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $path = defined('BASE_PATH')
            ? BASE_PATH . '/storage/database/sopima.sqlite'
            : dirname(__DIR__, 2) . '/storage/database/sopima.sqlite';
        $path = env('DB_SQLITE_PATH', $path);
        try {
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
        }
    }
    return $pdo;
}