#!/usr/bin/env php
<?php
/**
 * Sopima – Benachrichtigungs-Cronjob (Fristen via Notification-Channels)
 * Wird über bin/cron.php ausgeführt.
 */
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/bootstrap.php';

$pdo = db();

echo "[" . date('Y-m-d H:i:s') . "] " . APP_NAME . " Benachrichtigungs-Cronjob gestartet\n";

$users = $pdo->query("SELECT DISTINCT u.id, u.name, u.email FROM users u
    INNER JOIN notification_settings ns ON ns.user_id = u.id AND ns.enabled = 1
    WHERE u.active = 1")->fetchAll();

foreach ($users as $user) {
    $clientIds = $pdo->prepare("SELECT client_id FROM user_clients WHERE user_id = ?");
    $clientIds->execute([$user['id']]);
    $ids = $clientIds->fetchAll(PDO::FETCH_COLUMN);
    if (!$ids) continue;
    $in = implode(',', array_map('intval', $ids));

    $settingsStmt = $pdo->prepare("SELECT * FROM notification_settings WHERE user_id = ? AND enabled = 1");
    $settingsStmt->execute([$user['id']]);
    $settings = $settingsStmt->fetchAll();

    $allDays = [];
    foreach ($settings as $s) {
        foreach (explode(',', $s['days_before']) as $d) {
            $allDays[] = (int)$d;
        }
    }
    $allDays = array_unique($allDays);

    $contracts = [];
    foreach ($allDays as $days) {
        $stmt = $pdo->prepare("SELECT c.*, cl.name AS client_name,
                CAST(julianday(c.notice_date) - julianday('now') AS INTEGER) AS days_left
                FROM contracts c
                LEFT JOIN clients cl ON c.client_id = cl.id
                WHERE c.status = 'aktiv'
                  AND c.client_id IN ($in)
                  AND CAST(julianday(c.notice_date) - julianday('now') AS INTEGER) = ?");
        $stmt->execute([$days]);
        foreach ($stmt->fetchAll() as $contract) {
            $contracts[] = $contract;
        }
    }

    if (empty($contracts)) {
        echo "[" . date('H:i:s') . "] User {$user['name']}: keine Fristen heute\n";
        continue;
    }

    echo "[" . date('H:i:s') . "] User {$user['name']}: " . count($contracts) . " Frist(en) gefunden\n";

    $message = APP_NAME . " – Fristenwarnung\n\n";
    foreach ($contracts as $c) {
        $message .= "⚠ {$c['title']} ({$c['client_name']})\n";
        $message .= "  Kündigung bis: {$c['notice_date']} (noch {$c['days_left']} Tage)\n\n";
    }

    foreach ($settings as $s) {
        $config  = json_decode($s['config'], true) ?? [];
        $channel = $s['channel'];

        foreach ($contracts as $c) {
            $check = $pdo->prepare("SELECT id FROM notification_log WHERE user_id=? AND contract_id=? AND channel=? AND DATE(sent_at)=DATE('now')");
            $check->execute([$user['id'], $c['id'], $channel]);
            if ($check->fetch()) continue;

            $sent = false;

            if ($channel === 'email' && !empty($config['address'])) {
                $subject = APP_NAME . ": " . count($contracts) . " Frist(en) laufen bald ab";
                $sent = MailService::sendNotification($config['address'], '', $subject, $message);
            } elseif ($channel === 'discord' && !empty($config['webhook_url'])) {
                $payload = json_encode(['content' => "**" . APP_NAME . " Fristenwarnung**\n```\n{$message}\n```"]);
                $ch = curl_init($config['webhook_url']);
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true]);
                curl_exec($ch); $sent = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300; curl_close($ch);
            } elseif ($channel === 'telegram' && !empty($config['bot_token']) && !empty($config['chat_id'])) {
                $url = "https://api.telegram.org/bot{$config['bot_token']}/sendMessage";
                $payload = json_encode(['chat_id' => $config['chat_id'], 'text' => $message]);
                $ch = curl_init($url);
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true]);
                curl_exec($ch); $sent = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300; curl_close($ch);
            } elseif ($channel === 'ntfy' && !empty($config['url']) && !empty($config['topic'])) {
                $ch = curl_init(rtrim($config['url'], '/') . '/' . $config['topic']);
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $message, CURLOPT_HTTPHEADER => ['Title: ' . APP_NAME . ' Fristenwarnung', 'Priority: high'], CURLOPT_RETURNTRANSFER => true]);
                curl_exec($ch); $sent = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300; curl_close($ch);
            } elseif ($channel === 'gotify' && !empty($config['url']) && !empty($config['token'])) {
                $payload = json_encode(['title' => APP_NAME . ' Fristenwarnung', 'message' => $message, 'priority' => 7]);
                $ch = curl_init(rtrim($config['url'], '/') . '/message?token=' . $config['token']);
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true]);
                curl_exec($ch); $sent = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300; curl_close($ch);
            } elseif ($channel === 'pushover' && !empty($config['user_key']) && !empty($config['api_token'])) {
                $payload = ['token' => $config['api_token'], 'user' => $config['user_key'], 'title' => APP_NAME . ' Fristenwarnung', 'message' => $message];
                $ch = curl_init('https://api.pushover.net/1/messages.json');
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_RETURNTRANSFER => true]);
                curl_exec($ch); $sent = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300; curl_close($ch);
            } elseif ($channel === 'webhook' && !empty($config['url'])) {
                $payload = json_encode(['user' => $user['name'], 'contracts' => $contracts]);
                $ch = curl_init($config['url']);
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true]);
                curl_exec($ch); $sent = curl_getinfo($ch, CURLINFO_HTTP_CODE) < 300; curl_close($ch);
            }

            if ($sent) {
                $pdo->prepare("INSERT INTO notification_log (user_id, contract_id, channel) VALUES (?,?,?)")->execute([$user['id'], $c['id'], $channel]);
                echo "[" . date('H:i:s') . "] Gesendet via {$channel}: {$c['title']}\n";
            }
        }
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Cronjob beendet\n";