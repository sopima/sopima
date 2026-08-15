<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/tabler-icons.min.css">
</head>
<body>
<div class="app-layout">
    <?php require __DIR__ . '/nav.php'; ?>
    <div class="main-content">
    <div class="topbar">
        <h2><?php
            $uri = $_SERVER['REQUEST_URI'];
            if (str_contains($uri, 'contracts')) echo 'Verträge';
            elseif (str_contains($uri, 'clients')) echo 'Mandanten';
            elseif (str_contains($uri, 'settings')) echo 'Einstellungen';
            elseif (str_contains($uri, 'notifications')) echo 'Benachrichtigungen';
            else echo 'Dashboard';
        ?></h2>
        <form method="GET" action="/contracts" style="display:flex;align-items:center;gap:.5rem;position:absolute;left:50%;transform:translateX(-50%);">
            <div style="position:relative;">
                <i class="ti ti-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.95rem;pointer-events:none;"></i>
                <input type="text" name="q" placeholder="Verträge suchen…" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="padding:.4rem .75rem .4rem 2.1rem;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;font-size:.85rem;width:220px;outline:none;">
            </div>
        </form>
    </div>
    <div class="page-body">
