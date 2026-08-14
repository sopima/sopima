<div class="page-header">
    <h2>Einstellungen</h2>
</div>

<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
    <a href="/settings?tab=users" class="btn <?php echo $tab === 'users' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-users"></i> Benutzer
    </a>
    <a href="/settings?tab=tokens" class="btn <?php echo $tab === 'tokens' ? 'btn-primary' : 'btn-outline'; ?>">
        <i class="ti ti-key"></i> API-Tokens
    </a>
    <a href="/backup" class="btn btn-outline">
        <i class="ti ti-database"></i> Datensicherung
    </a>
</div>
