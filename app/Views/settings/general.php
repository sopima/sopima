<?php
$envPath = BASE_PATH . '/.env';
$saved = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = [];
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $current[trim($k)] = trim($v);
    }

    $current['APP_NAME']       = trim($_POST['app_name'] ?? $current['APP_NAME'] ?? 'Sopima');
    $current['APP_URL']        = rtrim(trim($_POST['app_url'] ?? $current['APP_URL'] ?? ''), '/');
    $current['APP_LOCALE']     = trim($_POST['app_locale'] ?? $current['APP_LOCALE'] ?? 'de');
    $current['MAIL_HOST']      = trim($_POST['mail_host'] ?? '');
    $current['MAIL_PORT']      = trim($_POST['mail_port'] ?? '587');
    $current['MAIL_USERNAME']  = trim($_POST['mail_user'] ?? '');
    $current['MAIL_FROM']      = trim($_POST['mail_from'] ?? '');
    $current['MAIL_FROM_NAME'] = trim($_POST['mail_name'] ?? '');

    $newPass = trim($_POST['mail_pass'] ?? '');
    if ($newPass !== '') {
        $current['MAIL_PASSWORD'] = $newPass;
    }

    $env = '';
    foreach ($current as $k => $v) {
        $env .= "{$k}={$v}\n";
    }

    if (file_put_contents($envPath, $env) !== false) {
        foreach ($current as $k => $v) {
            $_ENV[$k] = $v;
        }
        // Benachrichtigungseinstellungen in DB speichern
        $db = db();
        $days = (int)($_POST['notify_expiring_days'] ?? 30);
        $db->prepare("INSERT INTO settings (key, value, updated_at) VALUES ('notify_expiring_days', ?, datetime('now')) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=excluded.updated_at")
           ->execute([$days]);
        header('Location: /settings?tab=general&saved=1');
        exit;
    } else {
        $error = true;
    }
}
$saved = isset($_GET['saved']);

// Benachrichtigungseinstellungen laden
$db = db();
$notifyDays = (int)($db->query("SELECT value FROM settings WHERE key='notify_expiring_days'")->fetchColumn() ?: 30);

// Aktuelle Werte laden
$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v);
}
?>

<div class="page-header">
    <h2><?php echo __('settings.general.title'); ?></h2>
    <a href="/settings" class="btn btn-outline"><i class="ti ti-arrow-left"></i> <?php echo __('cf.back'); ?></a>
</div>

<?php if ($saved): ?>
<div class="alert alert-success" style="margin-bottom:1rem;"><i class="ti ti-check"></i> <?php echo __('settings.saved'); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error" style="margin-bottom:1rem;"><i class="ti ti-alert-circle"></i> <?php echo __('settings.error'); ?></div>
<?php endif; ?>

<form method="POST" action="/settings?tab=general">
    <div class="card" style="padding:1rem 1.25rem;margin-bottom:.75rem;">
        <h3 style="font-size:.88rem;font-weight:600;color:var(--text);margin-bottom:1rem;"><?php echo __('settings.general.title'); ?></h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.app_name'); ?></label>
                <input type="text" name="app_name" value="<?php echo htmlspecialchars($env['APP_NAME'] ?? 'Sopima'); ?>" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.app_url'); ?></label>
                <input type="url" name="app_url" value="<?php echo htmlspecialchars($env['APP_URL'] ?? ''); ?>" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.app_locale'); ?></label>
                <select name="app_locale">
                    <?php
                    $locales = [];
                    foreach (glob(BASE_PATH . '/lang/*.php') as $f) {
                        $locales[] = basename($f, '.php');
                    }
                    sort($locales);
                    foreach ($locales as $loc):
                    ?>
                    <option value="<?php echo $loc; ?>" <?php echo ($env['APP_LOCALE'] ?? 'de') === $loc ? 'selected' : ''; ?>>
                        <?php echo strtoupper($loc); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="card" style="padding:1rem 1.25rem;margin-bottom:.75rem;">
        <h3 style="font-size:.88rem;font-weight:600;color:var(--text);margin-bottom:1rem;"><?php echo __('settings.smtp_title'); ?></h3>
        <div style="display:grid;grid-template-columns:1fr auto;gap:.5rem;">
            <div class="form-group" style="margin:0;"><label><?php echo __('settings.smtp_host'); ?></label><input type="text" name="mail_host" value="<?php echo htmlspecialchars($env['MAIL_HOST'] ?? ''); ?>"></div>
            <div class="form-group" style="margin:0;"><label><?php echo __('settings.smtp_port'); ?></label><input type="number" name="mail_port" value="<?php echo htmlspecialchars($env['MAIL_PORT'] ?? '587'); ?>" style="width:80px;"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.smtp_user'); ?></label>
                <input type="text" name="mail_user" value="<?php echo htmlspecialchars($env['MAIL_USERNAME'] ?? ''); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.smtp_pass'); ?></label>
                <input type="password" name="mail_pass" placeholder="<?php echo __('settings.smtp_pass_hint'); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.mail_from'); ?></label>
                <input type="email" name="mail_from" value="<?php echo htmlspecialchars($env['MAIL_FROM'] ?? ''); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('settings.mail_name'); ?></label>
                <input type="text" name="mail_name" value="<?php echo htmlspecialchars($env['MAIL_FROM_NAME'] ?? ''); ?>">
            </div>
        </div>
        <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:1rem;">
            <form method="POST" action="/settings?tab=general&action=smtp_test" style="margin:0;">
                <button type="submit" class="btn btn-outline"><i class="ti ti-mail"></i> Test-Mail senden</button>
            </form>
            <span style="font-size:.75rem;color:var(--text-muted);">Zuerst speichern</span>
        </div>
    </div>



    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> <?php echo __('settings.save'); ?></button>
    </div>
</form>
<div style="display:flex;justify-content:flex-start;align-items:center;margin-top:.75rem;gap:1rem;">

</div>