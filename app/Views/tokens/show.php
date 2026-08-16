<div class="page-header">
    <h2><?php echo __('tokens.show.title'); ?></h2>
    <a href="/settings?tab=tokens" class="btn btn-outline"><i class="ti ti-arrow-left"></i> <?php echo __('tokens.show.back'); ?></a>
</div>
<div class="card" style="padding:1.5rem;">
    <div style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <div style="font-size:.78rem;font-weight:600;color:#34d399;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">
            <i class="ti ti-alert-triangle" style="vertical-align:-2px;"></i> <?php echo __('tokens.show.warning'); ?>
        </div>
        <div id="token-value" style="font-family:monospace;font-size:.95rem;color:#fff;word-break:break-all;background:rgba(0,0,0,.2);padding:.75rem 1rem;border-radius:8px;margin-top:.5rem;">
            <?php echo htmlspecialchars($plainToken); ?>
        </div>
        <button id="copy-btn" class="btn btn-outline" style="margin-top:.75rem;font-size:.82rem;">
            <i class="ti ti-copy"></i> <?php echo __('tokens.show.copy'); ?>
        </button>
        <script>
        document.getElementById('copy-btn').addEventListener('click', function() {
            var token = <?php echo json_encode($plainToken); ?>;
            var btn = this;
            navigator.clipboard.writeText(token).then(function() {
                btn.innerHTML = '<i class="ti ti-check"></i> <?php echo addslashes(__('tokens.show.copied')); ?>';
                setTimeout(function() {
                    btn.innerHTML = '<i class="ti ti-copy"></i> <?php echo addslashes(__('tokens.show.copy')); ?>';
                }, 2000);
            });
        });
        </script>
    </div>
    <table style="width:100%;font-size:.88rem;">
        <tr><td style="padding:.5rem 0;color:var(--text-muted);width:140px;"><?php echo __('tokens.show.name'); ?></td><td><?php echo htmlspecialchars($apiToken['name']); ?></td></tr>
        <tr><td style="padding:.5rem 0;color:var(--text-muted);"><?php echo __('tokens.show.client'); ?></td><td><?php echo htmlspecialchars($apiToken['client_name'] ?? __('tokens.show.all')); ?></td></tr>
        <tr><td style="padding:.5rem 0;color:var(--text-muted);"><?php echo __('tokens.show.perms'); ?></td><td>
            <?php foreach (json_decode($apiToken['permissions'], true) as $p): ?>
                <span class="badge badge-days-green" style="margin-right:.2rem;"><?php echo $p; ?></span>
            <?php endforeach; ?>
        </td></tr>
        <tr><td style="padding:.5rem 0;color:var(--text-muted);"><?php echo __('tokens.show.created'); ?></td><td><?php echo $apiToken['created_at']; ?></td></tr>
    </table>
</div>