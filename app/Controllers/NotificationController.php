<?php
$user = currentUser();
$db   = db();

$channels = ['email', 'discord', 'telegram', 'ntfy', 'gotify', 'pushover', 'webhook'];
// AJAX Test-Endpoint
if (isset($_GET['action']) && $_GET['action'] === 'test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $channel = $_POST['channel'] ?? '';
    $allowed = ['email','discord','telegram','ntfy','gotify','pushover','webhook'];
    if (!in_array($channel, $allowed)) {
        echo json_encode(['ok' => false, 'msg' => 'Unbekannter Kanal']);
        exit;
    }
    $stmt = $db->prepare("SELECT config FROM notification_settings WHERE user_id = ? AND channel = ?");
    $stmt->execute([$user['id'], $channel]);
    $row = $stmt->fetch();
    if (!$row) {
        echo json_encode(['ok' => false, 'msg' => 'Keine gespeicherte Konfiguration – bitte zuerst speichern']);
        exit;
    }
    $config = json_decode($row['config'], true) ?? [];
    $message = APP_NAME . " – Testnachricht\nDieser Kanal ist korrekt konfiguriert.";
    $sent = false; $error = '';

    if ($channel === 'email' && !empty($config['address'])) {
        $sent = MailService::sendNotification($config['address'], '', APP_NAME . " Testbenachrichtigung", "Dieser Kanal ist korrekt konfiguriert.");
        if (!$sent) $error = 'MailService fehlgeschlagen – siehe error_log';
    } elseif ($channel === 'discord' && !empty($config['webhook_url'])) {
        $payload = json_encode(['content' => "**" . APP_NAME . " Test**\n" . $message]);
        $ch = curl_init($config['webhook_url']);
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
        $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $sent = $code >= 200 && $code < 300; if (!$sent) $error = "HTTP $code";
    } elseif ($channel === 'telegram' && !empty($config['bot_token']) && !empty($config['chat_id'])) {
        $url = "https://api.telegram.org/bot{$config['bot_token']}/sendMessage";
        $payload = json_encode(['chat_id' => $config['chat_id'], 'text' => $message]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
        $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = json_decode($res, true); curl_close($ch);
        $sent = $code === 200 && ($body['ok'] ?? false);
        if (!$sent) $error = $body['description'] ?? "HTTP $code";
    } elseif ($channel === 'ntfy' && !empty($config['url']) && !empty($config['topic'])) {
        $ch = curl_init(rtrim($config['url'],'/').'/'.$config['topic']);
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$message,CURLOPT_HTTPHEADER=>['Title: ' . APP_NAME . ' Test','Priority: default'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $sent = $code >= 200 && $code < 300; if (!$sent) $error = "HTTP $code";
    } elseif ($channel === 'gotify' && !empty($config['url']) && !empty($config['token'])) {
        $payload = json_encode(['title'=>APP_NAME . ' Test','message'=>$message,'priority'=>5]);
        $ch = curl_init(rtrim($config['url'],'/').'message?token='.$config['token']);
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $sent = $code >= 200 && $code < 300; if (!$sent) $error = "HTTP $code";
    } elseif ($channel === 'pushover' && !empty($config['user_key']) && !empty($config['api_token'])) {
        $payload = ['token'=>$config['api_token'],'user'=>$config['user_key'],'title'=>APP_NAME . ' Test','message'=>$message];
        $ch = curl_init('https://api.pushover.net/1/messages.json');
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $sent = $code === 200; if (!$sent) $error = "HTTP $code";
    } elseif ($channel === 'webhook' && !empty($config['url'])) {
        $payload = json_encode(['test'=>true,'user'=>$user['name'],'message'=>$message]);
        $ch = curl_init($config['url']);
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10]);
        curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $sent = $code >= 200 && $code < 300; if (!$sent) $error = "HTTP $code";
    } else {
        $error = __('notify.test.incomplete');
    }

    echo json_encode(['ok' => $sent, 'msg' => $sent ? __('notify.test.sent') : ($error ?: __('notify.test.error'))]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($channels as $channel) {
        $enabled = isset($_POST['enabled'][$channel]) ? 1 : 0;
        $config  = [];

        if ($channel === 'email') {
            $config = ['address' => $_POST['config'][$channel]['address'] ?? ''];
        } elseif ($channel === 'discord') {
            $config = ['webhook_url' => $_POST['config'][$channel]['webhook_url'] ?? ''];
        } elseif ($channel === 'telegram') {
            $config = ['bot_token' => $_POST['config'][$channel]['bot_token'] ?? '', 'chat_id' => $_POST['config'][$channel]['chat_id'] ?? ''];
        } elseif ($channel === 'ntfy') {
            $config = ['url' => $_POST['config'][$channel]['url'] ?? '', 'topic' => $_POST['config'][$channel]['topic'] ?? ''];
        } elseif ($channel === 'gotify') {
            $config = ['url' => $_POST['config'][$channel]['url'] ?? '', 'token' => $_POST['config'][$channel]['token'] ?? ''];
        } elseif ($channel === 'pushover') {
            $config = ['user_key' => $_POST['config'][$channel]['user_key'] ?? '', 'api_token' => $_POST['config'][$channel]['api_token'] ?? ''];
        } elseif ($channel === 'webhook') {
            $config = ['url' => $_POST['config'][$channel]['url'] ?? ''];
        }

        $days = implode(',', array_filter(array_map('intval', $_POST['days_before'] ?? [])));
        if (!$days) $days = '7,30';

        $stmt = $db->prepare("INSERT INTO notification_settings (user_id, channel, enabled, config, days_before)
                               VALUES (?,?,?,?,?)
                               ON CONFLICT(user_id, channel) DO UPDATE SET enabled=excluded.enabled, config=excluded.config, days_before=excluded.days_before");
        $stmt->execute([
            $user['id'], $channel, $enabled, json_encode($config), $days,
            $enabled, json_encode($config), $days
        ]);
    }
    header('Location: /notifications?saved=1');
    exit;
}

$settings = [];
$stmt = $db->prepare("SELECT * FROM notification_settings WHERE user_id = ?");
$stmt->execute([$user['id']]);
foreach ($stmt->fetchAll() as $s) {
    $settings[$s['channel']] = $s;
    $settings[$s['channel']]['config'] = json_decode($s['config'], true) ?? [];
}

$saved = isset($_GET['saved']);

require __DIR__ . '/../Views/layouts/main.php';
require __DIR__ . '/../Views/notifications/index.php';
require __DIR__ . '/../Views/layouts/footer.php';