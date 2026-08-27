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
        <div class="topbar-left">
            <div class="topbar-title"><?php
                $uri = $_SERVER['REQUEST_URI'];
                if (str_contains($uri, 'contracts')) echo __('topbar.contracts');
                elseif (str_contains($uri, 'clients')) echo __('topbar.clients');
                elseif (str_contains($uri, 'backup')) echo __('topbar.settings');
                elseif (str_contains($uri, 'settings')) echo __('topbar.settings');
                elseif (str_contains($uri, 'notifications')) echo __('topbar.notifications');
                else echo __('topbar.dashboard');
            ?></div>
            <div class="topbar-sub"><?php
                if (str_contains($uri, 'contracts')) echo __('topbar.sub.contracts');
                elseif (str_contains($uri, 'clients')) echo __('topbar.sub.clients');
                elseif (str_contains($uri, 'backup')) echo __('topbar.sub.settings');
                elseif (str_contains($uri, 'settings')) echo __('topbar.sub.settings');
                elseif (str_contains($uri, 'notifications')) echo __('topbar.sub.notifications');
                else echo __('topbar.sub.dashboard');
            ?></div>
        </div>
        <form method="GET" action="/contracts" style="display:flex;align-items:center;gap:.5rem;position:absolute;left:50%;transform:translateX(-50%);">
            <div style="position:relative;">
                <i class="ti ti-search" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.95rem;pointer-events:none;"></i>
                <input type="text" name="q" placeholder="<?php echo __('topbar.search'); ?>" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="">
            </div>
        </form>
    </div>
    <div class="page-body">
