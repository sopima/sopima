<div class="page-header">
    <h2><?php echo $client ? __('clients.form.edit') : __('clients.form.new'); ?></h2>
    <div style="display:flex;gap:.5rem;">
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="/clients?action=types" class="btn btn-outline"><i class="ti ti-tag"></i><?php echo __('clients.form.types'); ?></a>
        <?php endif; ?>
        <a href="/clients" class="btn btn-outline"><i class="ti ti-arrow-left"></i><?php echo __('clients.form.back'); ?></a>
    </div>
</div>

<div class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto;">
    <form method="POST" action="/clients">
        <input type="hidden" name="action" value="<?php echo $client ? 'update' : 'store'; ?>">
        <?php if ($client): ?>
            <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label><?php echo __('clients.form.name'); ?></label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($client['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><?php echo __('clients.form.type'); ?></label>
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
            <label><?php echo __('clients.form.desc'); ?></label>
            <textarea name="description" rows="3"><?php echo htmlspecialchars($client['description'] ?? ''); ?></textarea>
        </div>


        <div class="form-group" style="margin-top:1rem;">
            <label style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.75rem;display:block;"><?php echo __('clients.form.address'); ?></label>
            <div style="display:grid;grid-template-columns:1fr;gap:1rem;">
                <div class="form-group">
                    <label><?php echo __('clients.form.street'); ?></label>
                    <input type="text" name="street" value="<?php echo htmlspecialchars($client['street'] ?? ''); ?>">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem;margin-top:1rem;">
                <div class="form-group">
                    <label><?php echo __('clients.form.zip'); ?></label>
                    <input type="text" name="zip" value="<?php echo htmlspecialchars($client['zip'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('clients.form.city'); ?></label>
                    <input type="text" name="city" value="<?php echo htmlspecialchars($client['city'] ?? ''); ?>">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-top:1rem;">
                <div class="form-group">
                    <label><?php echo __('clients.form.email'); ?></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($client['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('clients.form.phone'); ?></label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label><?php echo __('clients.form.website'); ?></label>
                    <input type="text" name="website" value="<?php echo htmlspecialchars($client['website'] ?? ''); ?>">
                </div>
            </div>
        </div>
        <?php if ($client): ?>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.88rem;color:var(--text-muted);">
                <input type="checkbox" name="active" value="1" <?php echo $client['active'] ? 'checked' : ''; ?> style="width:auto;">
                <?php echo __('clients.form.active'); ?>
            </label>
        </div>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: .5rem;">
            <a href="/clients" class="btn btn-outline"><?php echo __('clients.form.cancel'); ?></a>
            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i><?php echo __('clients.form.save'); ?></button>
        </div>
    </form>
</div>
