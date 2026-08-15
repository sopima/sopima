<aside class="sidebar">
    <div class="logo"><?php echo APP_NAME; ?></div>
    <nav>
        <a href="/dashboard" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'dashboard') || $_SERVER['REQUEST_URI'] === '/' ? 'active' : ''; ?>">
            <i class="ti ti-layout-dashboard"></i> Dashboard
        </a>
        <a href="/contracts" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'contracts') ? 'active' : ''; ?>">
            <i class="ti ti-file-description"></i> Verträge
        </a>
        <a href="/clients" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'clients') ? 'active' : ''; ?>">
            <i class="ti ti-users"></i> Mandanten
        </a>
        <a href="/notifications" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'notifications') ? 'active' : ''; ?>">
            <i class="ti ti-bell"></i> Benachrichtigungen
        </a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="/settings" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'settings') ? 'active' : ''; ?>">
            <i class="ti ti-settings"></i> Einstellungen
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
        <a href="/logout"><i class="ti ti-logout" style="font-size:13px;vertical-align:-1px"></i> Abmelden</a>
    </div>
</aside>
