<div class="page-header">
    <h2><?php echo $client ? 'Mandant bearbeiten' : 'Neuer Mandant'; ?></h2>
    <div style="display:flex;gap:.5rem;">
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="/clients?action=types" class="btn btn-outline"><i class="ti ti-tag"></i> Typen</a>
        <?php endif; ?>
        <a href="/clients" class="btn btn-outline"><i class="ti ti-arrow-left"></i> Zurück</a>
    </div>
</div>

<div class="card" style="padding: 1.5rem;">
    <form method="POST" action="/clients">
        <input type="hidden" name="action" value="<?php echo $client ? 'update' : 'store'; ?>">
        <?php if ($client): ?>
            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($client['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Typ *</label>
                <select name="type" required>
                    <?php foreach ($clientTypes as $t): ?>
                        <option value="<?php echo htmlspecialchars($t['name']); ?>" <?php echo ($client['type'] ?? '') === $t['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Beschreibung</label>
            <textarea name="description" rows="3"><?php echo htmlspecialchars($client['description'] ?? ''); ?></textarea>
        </div>

        <?php if ($client): ?>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.88rem;color:var(--text-muted);">
                <input type="checkbox" name="active" value="1" <?php echo $client['active'] ? 'checked' : ''; ?> style="width:auto;">
                Mandant aktiv
            </label>
        </div>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: .5rem;">
            <a href="/clients" class="btn btn-outline">Abbrechen</a>
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Speichern</button>
        </div>
    </form>
</div>
