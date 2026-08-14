<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sopima – Login</title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/tabler-icons.min.css">
</head>
<body class="login-page">
    <div class="login-box">
        <h1>Sopima</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="/login" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <!-- Honeypot: für Menschen unsichtbar, Bots füllen es aus -->
            <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;height:0;overflow:hidden;" aria-hidden="true" tabindex="-1">
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" required autofocus autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Anmelden</button>
        </form>
    </div>
</body>
</html>