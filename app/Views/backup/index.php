<?php require BASE_PATH . '/app/Views/settings/tabs.php'; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger mb-4">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div style="display:flex;flex-direction:column;gap:1.5rem;max-width:720px;">

    <!-- Export -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-download" style="margin-right:.4rem;opacity:.7;"></i><?php echo __('backup.export_title'); ?></h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                <?php echo __('backup.export_desc'); ?>
            </p>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <form method="POST" action="/backup?action=export-json" style="display:flex;flex-direction:column;gap:.5rem;max-width:400px;">
                    <label style="font-size:.8rem;color:var(--text-muted);"><?php echo __('backup.password_optional'); ?></label>
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        <input type="password" name="backup_password" placeholder="<?php echo htmlspecialchars(__('backup.password_placeholder')); ?>"
                               style="flex:1;padding:.4rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.85rem;">
                        <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                            <i class="ti ti-file-type-json"></i><?php echo __('backup.export_json'); ?>
                        </button>
                    </div>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:0;"><?php echo __('backup.password_hint'); ?></p>
                </form>
                <hr style="border:none;border-top:1px solid rgba(255,255,255,.08);margin:.25rem 0;">
                <form method="POST" action="/backup?action=export-csv" style="display:flex;flex-direction:column;gap:.3rem;">
                    <button type="submit" class="btn btn-outline">
                        <i class="ti ti-file-zip"></i><?php echo __('backup.export_csv'); ?>
                    </button>
                    <p style="font-size:.75rem;color:var(--text-muted);margin:.25rem 0 0 .5rem;"><?php echo __('backup.csv_no_encryption'); ?></p>
                </form>
            </div>
        </div>
    </div>

    <!-- Import -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-upload" style="margin-right:.4rem;opacity:.7;"></i><?php echo __('backup.import_title'); ?></h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-4" style="display:flex;gap:.75rem;align-items:flex-start;">
                <i class="ti ti-alert-triangle" style="font-size:1.2rem;flex-shrink:0;margin-top:.1rem;"></i>
                <div>
                    <?php echo __('backup.import_warning'); ?>
                </div>
            </div>
            <form method="POST" action="/backup?action=import" enctype="multipart/form-data">
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label" style="display:block;margin-bottom:.5rem;"><?php echo __('backup.file_label'); ?></label>
                    <label id="file-drop" style="
                        display:flex;flex-direction:column;align-items:center;justify-content:center;
                        gap:.5rem;padding:2rem 1.5rem;border-radius:.75rem;cursor:pointer;
                        border:2px dashed rgba(255,255,255,.15);
                        background:rgba(255,255,255,.04);
                        transition:border-color .2s,background .2s;
                    " onmouseover="this.style.borderColor='rgba(255,255,255,.3)';this.style.background='rgba(255,255,255,.07)'"
                       onmouseout="this.style.borderColor='rgba(255,255,255,.15)';this.style.background='rgba(255,255,255,.04)'">
                        <i class="ti ti-file-import" style="font-size:2rem;opacity:.5;"></i>
                        <span id="file-label" style="font-size:.9rem;color:var(--text-muted);"><?php echo __('backup.file_drop'); ?></span>
                        <span style="font-size:.78rem;opacity:.45;">.json / .enc</span>
                        <input type="file" name="backup_file" accept=".json,.enc" required
                               style="position:absolute;width:1px;height:1px;opacity:0;"
                               onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? this.dataset.nofile;" data-nofile="<?php echo htmlspecialchars(__('cf.file.no_file')); ?>">
                    </label>
                    <p style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem;">
                        <?php echo APP_NAME; ?> <?php echo __('backup.file_hint'); ?>
                    </p>
                </div>
                <div id="enc-password-wrap" style="display:none;margin-bottom:1rem;">
                    <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('backup.restore_password'); ?></label>
                    <input type="password" name="restore_password" id="restore_password"
                           style="padding:.4rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.85rem;width:100%;box-sizing:border-box;">
                </div>
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm(this.dataset.confirm)" data-confirm="<?php echo __('backup.confirm_import'); ?>">
                    <i class="ti ti-upload"></i><?php echo __('backup.import_btn'); ?>
                </button>
            </form>
        </div>
    </div>

</div>
<script>
document.querySelector('input[name="backup_file"]').addEventListener('change', function() {
    var wrap = document.getElementById('enc-password-wrap');
    var pwField = document.getElementById('restore_password');
    var isEnc = this.files[0] && this.files[0].name.endsWith('.enc');
    wrap.style.display = isEnc ? 'block' : 'none';
    pwField.required = isEnc;
});
</script>