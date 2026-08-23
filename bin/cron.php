#!/usr/bin/env php
<?php
/**
 * Sopima – Cron Runner
 * Aufruf: php bin/cron.php
 * Cronjob (täglich): 0 7 * * * php /var/www/html/bin/cron.php
 */
$binDir = __DIR__;
$jobs = [
    'notify.php',
    'notify-expiring.php',
];

echo "[" . date('Y-m-d H:i:s') . "] Sopima Cron Runner gestartet\n";
echo "---------------------------------------------------\n";

foreach ($jobs as $job) {
    $file = $binDir . '/' . $job;
    if (!file_exists($file)) {
        echo "[SKIP] {$job} – Datei nicht gefunden\n";
        continue;
    }
    echo "[RUN]  {$job}\n";
    passthru(PHP_BINARY . ' ' . escapeshellarg($file), $rc);
    echo "[DONE] {$job} – Exit-Code: {$rc}\n";
    echo "---------------------------------------------------\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Cron Runner beendet\n";
