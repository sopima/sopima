<div class="page-header">
    <h2><?php echo $editUser ? 'Benutzer bearbeiten' : 'Neuer Benutzer'; ?></h2>
    <a href="/settings?tab=users" class="btn btn-outline"><i class="ti ti-arrow-left"></i> Zurück</a>
</div>

<div class="card" style="padding: 1.5rem;">
    <form method="POST" action="/settings?tab=users">
        <input type="hidden" name="action" value="<?php echo $editUser ? 'update' : 'store'; ?>">
        <?php if ($editUser): ?>
            <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($editUser['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>E-Mail *</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo $editUser ? 'Neues Passwort (leer = unverändert)' : 'Passwort *'; ?></label>
                <input type="password" name="password" <?php echo $editUser ? '' : 'required'; ?>>
            </div>
            <div class="form-group">
                <label>Rolle *</label>
                <select name="role" required>
                    <option value="user"  <?php echo ($editUser['role'] ?? 'user') === 'user'  ? 'selected' : ''; ?>>Benutzer</option>
                    <option value="admin" <?php echo ($editUser['role'] ?? '')      === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
        </div>

        <?php if ($editUser): ?>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.88rem;color:var(--text-muted);">
                <input type="checkbox" name="active" value="1" <?php echo $editUser['active'] ? 'checked' : ''; ?> style="width:auto;">
                Benutzer aktiv
            </label>
        </div>
        <?php endif; ?>

        <div class="form-group" style="margin-top:.5rem;">
            <label>Mandanten-Zugriff</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.5rem;margin-top:.5rem;">
                <?php foreach ($allClients as $cl): ?>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.88rem;color:var(--text-muted);background:rgba(255,255,255,.05);padding:.5rem .75rem;border-radius:8px;border:1px solid rgba(255,255,255,.1);">
                    <input type="checkbox" name="clients[]" value="<?php echo $cl['id']; ?>"
                        <?php echo in_array($cl['id'], $userClients) ? 'checked' : ''; ?>
                        style="width:auto;">
                    <?php echo htmlspecialchars($cl['name']); ?>
                    <span style="font-size:.72rem;color:var(--text-sub)">(<?php echo ucfirst($cl['type']); ?>)</span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
            <a href="/settings?tab=users" class="btn btn-outline">Abbrechen</a>
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Speichern</button>
        </div>
    </form>
</div>
