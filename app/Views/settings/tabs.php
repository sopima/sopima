<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentTab = $_GET['tab'] ?? '';
?>
<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="/settings?tab=general" class="btn <?php echo $currentTab === 'general' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-adjustments"></i> <?php echo __('settings.general'); ?>
    </a>
    <a href="/settings?tab=users" class="btn <?php echo $currentTab === 'users' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-users"></i><?php echo __('settings.users'); ?>
    </a>
    <a href="/settings?tab=tokens" class="btn <?php echo $currentTab === 'tokens' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-key"></i><?php echo __('settings.tokens'); ?>
    </a>
    <a href="/settings?tab=mail" class="btn <?php echo $currentTab === 'mail' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-mail"></i><?php echo __('settings.mail_templates'); ?>
    </a>
    <a href="/settings/letter-templates" class="btn <?php echo $currentPath === '/settings/letter-templates' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-file-invoice"></i> <?php echo __('settings.letter_templates'); ?>
    </a>
    <a href="/settings?tab=pdf" class="btn <?php echo $currentTab === 'pdf' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-file-text"></i><?php echo __('settings.pdf_templates'); ?>
    </a>
    <a href="/backup" class="btn <?php echo $currentPath === '/backup' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-database"></i><?php echo __('settings.backup'); ?>
    </a>
</div>