<div class="page-header">
    <h2><?php echo __('settings.title'); ?></h2>
</div>

<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
    <a href="/settings?tab=general" class="btn <?php echo $tab === 'general' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-adjustments"></i> <?php echo __('settings.general'); ?>
    </a>
    <a href="/settings?tab=users" class="btn <?php echo $tab === 'users' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-users"></i><?php echo __('settings.users'); ?>
    </a>
    <a href="/settings?tab=tokens" class="btn <?php echo $tab === 'tokens' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-key"></i><?php echo __('settings.tokens'); ?>
    </a>
    <a href="/settings?tab=mail" class="btn <?php echo $tab === 'mail' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-mail"></i><?php echo __('settings.mail_templates'); ?>
    </a>
    <a href="/backup" class="btn btn-outline">
        <i class="ti ti-database"></i><?php echo __('settings.backup'); ?>
    </a>
</div>
