<div class="page-header">
    <h2>Neuer API-Token</h2>
    <a href="/settings?tab=tokens" class="btn btn-outline"><i class="ti ti-arrow-left"></i> Zurück</a>
</div>

<div class="card" style="padding:1.5rem;">
    <form method="POST" action="/settings?tab=tokens">
        <input type="hidden" name="action" value="store">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required placeholder="z.B. z.B. Mein System">
            </div>
            <div class="form-group">
                <label>Mandant (optional)</label>
                <select name="client_id">
                    <option value="">Alle Mandanten</option>
                    <?php foreach ($allClients as $cl): ?>
                        <option value="<?php echo $cl['id']; ?>"><?php echo htmlspecialchars($cl['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Berechtigungen *</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.5rem;margin-top:.5rem;">
                <?php foreach ($allPerms as $p): ?>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.88rem;color:var(--text-muted);background:rgba(255,255,255,.05);padding:.5rem .75rem;border-radius:8px;border:1px solid rgba(255,255,255,.1);">
                    <input type="checkbox" name="permissions[]" value="<?php echo $p; ?>" style="width:auto;">
                    <?php echo $p; ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display:flex;gap:1rem;justify-content:flex-end;margin-top:1rem;">
            <a href="/settings?tab=tokens" class="btn btn-outline">Abbrechen</a>
            <button type="submit" class="btn btn-primary"><i class="ti ti-key"></i> Token generieren</button>
        </div>
    </form>
</div>
