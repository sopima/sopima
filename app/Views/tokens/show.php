<div class="page-header">
    <h2>Token erstellt</h2>
    <a href="/settings?tab=tokens" class="btn btn-outline"><i class="ti ti-arrow-left"></i> Zurück</a>
</div>

<div class="card" style="padding:1.5rem;">
    <div style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem;">
        <div style="font-size:.78rem;font-weight:600;color:#34d399;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">
            <i class="ti ti-alert-triangle" style="vertical-align:-2px;"></i> Nur einmal sichtbar – jetzt kopieren!
        </div>
        <div style="font-family:monospace;font-size:.95rem;color:#fff;word-break:break-all;background:rgba(0,0,0,.2);padding:.75rem 1rem;border-radius:8px;margin-top:.5rem;">
            <?php echo htmlspecialchars($plainToken); ?>
        </div>
        <button onclick="navigator.clipboard.writeText('<?php echo $plainToken; ?>');this.innerHTML='<i class=\'ti ti-check\'></i> Kopiert!';" class="btn btn-outline" style="margin-top:.75rem;font-size:.82rem;">
            <i class="ti ti-copy"></i> Kopieren
        </button>
    </div>

    <table style="width:100%;font-size:.88rem;">
        <tr><td style="padding:.5rem 0;color:var(--text-muted);width:140px;">Name</td><td><?php echo htmlspecialchars($apiToken['name']); ?></td></tr>
        <tr><td style="padding:.5rem 0;color:var(--text-muted);">Mandant</td><td><?php echo htmlspecialchars($apiToken['client_name'] ?? 'Alle'); ?></td></tr>
        <tr><td style="padding:.5rem 0;color:var(--text-muted);">Berechtigungen</td><td>
            <?php foreach (json_decode($apiToken['permissions'], true) as $p): ?>
                <span class="badge badge-days-green" style="margin-right:.2rem;"><?php echo $p; ?></span>
            <?php endforeach; ?>
        </td></tr>
        <tr><td style="padding:.5rem 0;color:var(--text-muted);">Erstellt</td><td><?php echo $apiToken['created_at']; ?></td></tr>
    </table>
</div>