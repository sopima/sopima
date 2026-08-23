#!/usr/bin/env php
<?php
/**
 * Sopima – Ablaufbenachrichtigung
 * Aufruf: php bin/notify-expiring.php
 * Cronjob (täglich): 0 7 * * * php /var/www/html/bin/notify-expiring.php
 */
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

$db   = db();
$days = (int)($db->query("SELECT value FROM settings WHERE key='notify_expiring_days'")->fetchColumn() ?: $_ENV['NOTIFY_EXPIRING_DAYS'] ?? 30);

echo "[notify-expiring] Prüfe Verträge die in {$days} Tagen ablaufen...\n";

$targetDate = date('Y-m-d', strtotime("+{$days} days"));

$stmt = $db->prepare("
    SELECT c.*, cat.name as category
    FROM contracts c
    LEFT JOIN categories cat ON c.category_id = cat.id
    WHERE c.end_date = ?
      AND c.status = 'aktiv'
");
$stmt->execute([$targetDate]);
$contracts = $stmt->fetchAll();

echo "[notify-expiring] " . count($contracts) . " Vertrag/Verträge gefunden.\n";

foreach ($contracts as $contract) {
    // Empfänger: Kontakt-Email
    $toEmail = null;
    $cont = $db->prepare("SELECT email FROM contacts WHERE contract_id = ? AND email IS NOT NULL AND email != '' LIMIT 1");
    $cont->execute([$contract['id']]);
    $cRow = $cont->fetch();
    if ($cRow && filter_var($cRow['email'], FILTER_VALIDATE_EMAIL)) {
        $toEmail = $cRow['email'];
    }

    if (!$toEmail) {
        echo "[notify-expiring] Vertrag #{$contract['id']} ({$contract['title']}): kein Empfänger, übersprungen.\n";
        continue;
    }

    $ok = MailService::sendContractEvent('contract.expiring', $toEmail, (array)$contract);
    echo "[notify-expiring] Vertrag #{$contract['id']} ({$contract['title']}) → {$toEmail}: " . ($ok ? "OK" : "FEHLER") . "\n";
}

echo "[notify-expiring] Fertig.\n";