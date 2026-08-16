<?php
declare(strict_types=1);

// Bereits installiert? Admin vorhanden → /login
function setupIsComplete(): bool {
    try {
        $pdo = db();
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        return (int)$count > 0;
    } catch (Throwable) {
        return false;
    }
}

if (setupIsComplete()) {
    header('Location: /login');
    exit;
}

load_lang(env('APP_LOCALE', 'de'));
load_lang(env('APP_LOCALE', 'de'));
$step   = (int)($_GET['step'] ?? 1);
$errors = [];

// ── Schritt 2: .env schreiben ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    $appName   = trim($_POST['app_name']   ?? 'Sopima');
    $appUrl    = rtrim(trim($_POST['app_url'] ?? ''), '/');
    $appSecret = trim($_POST['app_secret'] ?? bin2hex(random_bytes(32)));
    $sqlitePath = trim($_POST['sqlite_path'] ?? BASE_PATH . '/storage/database/sopima.sqlite');
    $mailHost  = trim($_POST['mail_host']  ?? '');
    $mailPort  = trim($_POST['mail_port']  ?? '587');
    $mailUser  = trim($_POST['mail_user']  ?? '');
    $mailPass  = trim($_POST['mail_pass']  ?? '');
    $mailFrom  = trim($_POST['mail_from']  ?? '');
    $mailName  = trim($_POST['mail_name']  ?? $appName);
    $appLocale = trim($_POST['app_locale'] ?? 'de');

    if (empty($appUrl))    $errors[] = __('setup.error.app_url');
    if (empty($appSecret)) $errors[] = __('setup.error.app_secret');

    if (empty($errors)) {
        $env  = "APP_NAME={$appName}\n";
        $env .= "APP_URL={$appUrl}\n";
        $env .= "APP_SECRET={$appSecret}\n";
        $env .= "\n";
        $env .= "DB_SQLITE_PATH={$sqlitePath}\n";
        $env .= "\n";
        $env .= "MAIL_HOST={$mailHost}\n";
        $env .= "MAIL_PORT={$mailPort}\n";
        $env .= "MAIL_USERNAME={$mailUser}\n";
        $env .= "MAIL_PASSWORD={$mailPass}\n";
        $env .= "MAIL_FROM={$mailFrom}\n";
        $env .= "MAIL_FROM_NAME={$mailName}\n";

        $envPath = BASE_PATH . '/storage/.env';
        $written = file_put_contents($envPath, $env);
        if ($written === false || $written === 0) {
            $errors[] = '.env konnte nicht geschrieben werden. Pfad: ' . $envPath . ' | Schreibbar: ' . (is_writable(dirname($envPath)) ? 'ja' : 'nein');
        } else {
            // Auch direkt nach BASE_PATH schreiben damit db() sie findet
            file_put_contents(BASE_PATH . '/.env', $env);
            // $_ENV sofort aktualisieren
            foreach (explode("\n", $env) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim($v);
            }
            header('Location: /setup?step=3');
            exit;
        }
    }
}

// ── Schritt 3: Migrationen ─────────────────────────────────────────────────
$migrationLog = [];
if ($step === 3) {
    try {
        $pdo = db();
        $pdo->exec("PRAGMA foreign_keys = OFF");
        $pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (
            filename   TEXT NOT NULL PRIMARY KEY,
            applied_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");
        $applied = $pdo->query("SELECT filename FROM _migrations ORDER BY filename")
                       ->fetchAll(PDO::FETCH_COLUMN, 0);
        $files = glob(BASE_PATH . '/database/migrations/[0-9]*.sql');
        sort($files);
        foreach ($files as $filepath) {
            $name = basename($filepath);
            if (in_array($name, $applied)) {
                $migrationLog[] = ['status' => 'skip', 'name' => $name];
                continue;
            }
            $sql = file_get_contents($filepath);
            if (empty(trim($sql))) {
                $migrationLog[] = ['status' => 'empty', 'name' => $name];
                continue;
            }
            $pdo->exec($sql);
            $pdo->prepare("INSERT INTO _migrations (filename) VALUES (?)")->execute([$name]);
            $migrationLog[] = ['status' => 'ok', 'name' => $name];
        }
    
    // Seed-Daten sprachabhängig einfügen
    $locale = $_ENV['APP_LOCALE'] ?? 'de';
    $already = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
    if ((int)$already === 0) {
        if ($locale === 'en') {
            $clientSeeds = [['Private', 'privat'],['My Company', 'firma'],['My Club', 'verein']];
            $categorySeeds = [['Insurance','#3b82f6'],['Rent','#10b981'],['Software / Subscription','#8b5cf6'],['Energy','#f59e0b'],['Telecommunications','#ef4444'],['Services','#6366f1'],['Other','#6b7280']];
            $typeSeeds = ['Private','Company','HOA','Club'];
        } else {
            $clientSeeds = [['Privat', 'privat'],['Meine Firma', 'firma'],['Mein Verein', 'verein']];
            $categorySeeds = [['Versicherung','#3b82f6'],['Miete','#10b981'],['Software / Abo','#8b5cf6'],['Energie','#f59e0b'],['Telekommunikation','#ef4444'],['Dienstleistung','#6366f1'],['Sonstiges','#6b7280']];
            $typeSeeds = ['Privat','Firma','WEG','Verein'];
        }
        foreach ($clientSeeds as [$n, $t]) {
            $pdo->prepare("INSERT INTO clients (name, type) VALUES (?, ?)")->execute([$n, $t]);
        }
        foreach ($categorySeeds as [$n, $c]) {
            $pdo->prepare("INSERT INTO contract_categories (name, color) VALUES (?, ?)")->execute([$n, $c]);
        }
        foreach ($typeSeeds as $t) {
            $pdo->prepare("INSERT OR IGNORE INTO client_types (name) VALUES (?)")->execute([$t]);
        }
    }
} catch (Throwable $e) {
        $errors[] = 'Migration fehlgeschlagen: ' . $e->getMessage();
    }
}

// ── Schritt 4: Admin anlegen ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 4) {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');

    if (empty($name))               $errors[] = __('setup.error.name');
    if (empty($email))              $errors[] = __('setup.error.email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = __('setup.error.email_invalid');
    if (strlen($password) < 8)      $errors[] = __('setup.error.password_short');
    if ($password !== $password2)   $errors[] = __('setup.error.password_match');

    if (empty($errors)) {
        try {
            $pdo  = db();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO users (name, email, password_hash, role, active) VALUES (?, ?, ?, 'admin', 1)")
                ->execute([$name, $email, $hash]);
            $adminId = $pdo->lastInsertId();
            // Admin allen bestehenden Mandanten zuweisen
            $clients = $pdo->query("SELECT id FROM clients")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($clients as $clientId) {
                $pdo->prepare("INSERT OR IGNORE INTO user_clients (user_id, client_id) VALUES (?, ?)")
                    ->execute([$adminId, $clientId]);
            }
            header('Location: /setup?step=5');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Fehler beim Anlegen: ' . $e->getMessage();
        }
    }
}

// ── HTML ───────────────────────────────────────────────────────────────────
$secret = bin2hex(random_bytes(32));
$defaultPath = BASE_PATH . '/storage/database/sopima.sqlite';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo APP_NAME; ?> – Setup</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .setup-wrap { max-width: 560px; margin: 4rem auto; padding: 0 1rem; }
        .setup-card { background: var(--surface); border-radius: 12px; padding: 2rem; }
        .setup-steps { display: flex; gap: .5rem; margin-bottom: 2rem; }
        .setup-step  { flex: 1; height: 4px; border-radius: 2px; background: var(--border); }
        .setup-step.done  { background: var(--accent); }
        .setup-step.active { background: var(--accent); opacity: .5; }
        .check { color: #34d399; margin-right: .4rem; }
        .fail  { color: #f87171; margin-right: .4rem; }
        .setup-card h2 { margin-bottom: 1.5rem; }
        .migration-log { font-family: monospace; font-size: .85rem; margin: 1rem 0; }
        .migration-log span { display: block; padding: .1rem 0; }
    </style>
</head>
<body>
<div class="setup-wrap">
    <div style="text-align:center;margin-bottom:2rem;">
        <div class="logo" style="font-size:1.5rem;font-weight:700;"><?php echo APP_NAME; ?></div>
        <div style="color:var(--text-muted);margin-top:.3rem;"><?php echo __('setup.title'); ?></div>
    </div>

    <div class="setup-steps">
        <?php foreach ([1,2,3,4,5] as $s): ?>
        <div class="setup-step <?php echo $s < $step ? 'done' : ($s === $step ? 'active' : ''); ?>"></div>
        <?php endforeach; ?>
    </div>

    <div class="setup-card">

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom:1.5rem;">
            <?php foreach ($errors as $e): ?>
                <div><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <?php
        $checks = [
            'PHP ≥ 8.2'          => version_compare(PHP_VERSION, '8.2.0', '>='),
            'pdo_sqlite'          => extension_loaded('pdo_sqlite'),
            'mbstring'            => extension_loaded('mbstring'),
            'fileinfo'            => extension_loaded('fileinfo'),
            'zip'                 => extension_loaded('zip'),
            __('setup.check.storage')          => is_writable(BASE_PATH . '/storage'),
            __('setup.check.storage_db') => is_writable(BASE_PATH . '/storage/database') || (!is_dir(BASE_PATH . '/storage/database') && is_writable(BASE_PATH . '/storage')),
            __('setup.check.storage_uploads')  => is_writable(BASE_PATH . '/storage/uploads')  || (!is_dir(BASE_PATH . '/storage/uploads')  && is_writable(BASE_PATH . '/storage')),
            __('setup.check.env')              => is_writable(BASE_PATH . '/storage'),
        ];
        $allOk = !in_array(false, $checks, true);
        ?>
        <h2><?php echo __('setup.system_check'); ?></h2>
        <?php foreach ($checks as $label => $ok): ?>
            <div style="margin-bottom:.5rem;">
                <span class="<?php echo $ok ? 'check' : 'fail'; ?>"><?php echo $ok ? '✓' : '✗'; ?></span>
                <?php echo htmlspecialchars($label); ?>
            </div>
        <?php endforeach; ?>
        <div style="margin-top:2rem;">
            <?php if ($allOk): ?>
                <a href="/setup?step=2" class="btn btn-primary" style="width:100%;display:block;text-align:center;"><?php echo __('setup.next'); ?></a>
            <?php else: ?>
                <div style="color:var(--text-muted);font-size:.9rem;margin-bottom:1rem;"><?php echo __('setup.fix_checks'); ?></div>
                <a href="/setup?step=1" class="btn btn-outline" style="width:100%;display:block;text-align:center;"><?php echo __('setup.recheck'); ?></a>
            <?php endif; ?>
        </div>

    <?php elseif ($step === 2): ?>
        <h2><?php echo __('setup.configuration'); ?></h2>
        <form method="post" action="/setup?step=2">
            <div class="form-group">
                <label><?php echo __('setup.label.app_name'); ?></label>
                <input type="text" name="app_name" value="<?php echo htmlspecialchars($_POST['app_name'] ?? 'Sopima'); ?>" required>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.app_url'); ?></label>
                <input type="url" name="app_url" value="<?php echo htmlspecialchars($_POST['app_url'] ?? ''); ?>" placeholder="https://ihre-domain.example.com" required>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.app_secret'); ?></label>
                <div style="display:flex;gap:.5rem;">
                    <input type="text" name="app_secret" id="app_secret" value="<?php echo htmlspecialchars($_POST['app_secret'] ?? $secret); ?>" required style="flex:1;font-family:monospace;font-size:.85rem;">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('app_secret').value='<?php echo bin2hex(random_bytes(32)); ?>'">↺</button>
                </div>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.locale'); ?></label>
                <select name="app_locale">
                    <option value="de" <?php echo ($_POST['app_locale'] ?? 'de') === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                    <option value="en" <?php echo ($_POST['app_locale'] ?? 'de') === 'en' ? 'selected' : ''; ?>>English</option>
                </select>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.locale'); ?></label>
                <select name="app_locale">
                    <option value="de" <?php echo ($_POST['app_locale'] ?? 'de') === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                    <option value="en" <?php echo ($_POST['app_locale'] ?? 'de') === 'en' ? 'selected' : ''; ?>>English</option>
                </select>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.sqlite_path'); ?></label>
                <input type="text" name="sqlite_path" value="<?php echo htmlspecialchars($_POST['sqlite_path'] ?? $defaultPath); ?>" required style="font-family:monospace;font-size:.85rem;">
            </div>
            <hr style="margin:1.5rem 0;border-color:var(--border);">
            <div style="color:var(--text-muted);font-size:.85rem;margin-bottom:1rem;"><?php echo __('setup.mail_optional'); ?></div>
            <div style="display:grid;grid-template-columns:1fr auto;gap:.5rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('setup.label.smtp_host'); ?></label><input type="text" name="mail_host" value="<?php echo htmlspecialchars($_POST['mail_host'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('setup.label.port'); ?></label><input type="number" name="mail_port" value="<?php echo htmlspecialchars($_POST['mail_port'] ?? '587'); ?>" style="width:80px;"></div>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.smtp_user'); ?></label>
                <input type="text" name="mail_user" value="<?php echo htmlspecialchars($_POST['mail_user'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.smtp_pass'); ?></label>
                <input type="password" name="mail_pass" value="">
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.mail_from'); ?></label>
                <input type="email" name="mail_from" value="<?php echo htmlspecialchars($_POST['mail_from'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.mail_name'); ?></label>
                <input type="text" name="mail_name" value="<?php echo htmlspecialchars($_POST['mail_name'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;"><?php echo __('setup.save_next'); ?></button>
        </form>

    <?php elseif ($step === 3): ?>
        <h2><?php echo __('setup.database'); ?></h2>
        <?php if (empty($errors)): ?>
            <div class="migration-log">
                <?php foreach ($migrationLog as $m): ?>
                    <span>
                        <?php if ($m['status'] === 'ok'):   echo '<span class="check">✓</span>'; endif; ?>
                        <?php if ($m['status'] === 'skip'):  echo '<span style="color:var(--text-muted);">–</span> '; endif; ?>
                        <?php if ($m['status'] === 'empty'): echo '<span style="color:var(--text-muted);">○</span> '; endif; ?>
                        <?php echo htmlspecialchars($m['name']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <a href="/setup?step=4" class="btn btn-primary" style="width:100%;display:block;text-align:center;margin-top:1.5rem;"><?php echo __('setup.next'); ?></a>
        <?php else: ?>
            <a href="/setup?step=3" class="btn btn-outline" style="width:100%;display:block;text-align:center;margin-top:1rem;"><?php echo __('setup.retry'); ?></a>
        <?php endif; ?>

    <?php elseif ($step === 4): ?>
        <h2><?php echo __('setup.admin'); ?></h2>
        <form method="post" action="/setup?step=4">
            <div class="form-group">
                <label><?php echo __('setup.label.name'); ?></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.email'); ?></label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.password'); ?></label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label><?php echo __('setup.label.password2'); ?></label>
                <input type="password" name="password2" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1rem;"><?php echo __('setup.create_admin'); ?></button>
        </form>

    <?php elseif ($step === 5): ?>
        <h2><?php echo __('setup.done'); ?></h2>
        <div style="text-align:center;padding:1rem 0;">
            <div style="font-size:3rem;margin-bottom:1rem;">✓</div>
            <p style="color:var(--text-muted);margin-bottom:2rem;"><?php echo APP_NAME; ?><?php echo __('setup.ready'); ?></p>
            <a href="/login" class="btn btn-primary" style="display:inline-block;padding:.75rem 2rem;"><?php echo __('setup.to_login'); ?></a>
        </div>
    <?php endif; ?>

    </div>
</div>
</body>
</html>
