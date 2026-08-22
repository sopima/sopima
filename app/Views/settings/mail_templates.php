<?php
$templates = $db->query("SELECT * FROM mail_templates ORDER BY event")->fetchAll();
$saved = $_GET['saved'] ?? '';
?>
<?php if ($saved === '1'): ?>
<div class="alert alert-success" style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:8px;color:#34d399;font-size:.88rem;">
    <?php echo __('settings.mail_saved'); ?>
</div>
<?php endif; ?>
<?php foreach ($templates as $tpl): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-head">
        <span><i class="ti ti-mail" style="vertical-align:-2px;margin-right:4px;color:#a5b4fc"></i>
        <?php echo __('settings.mail_event'); ?>: <code style="background:rgba(255,255,255,.07);padding:.1rem .4rem;border-radius:4px;font-size:.83rem;"><?php echo htmlspecialchars($tpl['event']); ?></code></span>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.83rem;color:var(--text-muted);">
            <input type="checkbox" form="form-<?php echo $tpl['id']; ?>" name="active" value="1" <?php echo $tpl['active'] ? 'checked' : ''; ?>>
            <?php echo __('settings.mail_active'); ?>
        </label>
    </div>
    <form id="form-<?php echo $tpl['id']; ?>" method="POST" action="/settings?tab=mail&action=save" style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1rem;">
        <input type="hidden" name="id" value="<?php echo $tpl['id']; ?>">
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.mail_subject'); ?></label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($tpl['subject']); ?>"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;">
                <?php echo __('settings.mail_body'); ?>
                <span style="margin-left:.75rem;opacity:.6;"><?php echo __('settings.mail_placeholders'); ?>: {{title}} {{partner}} {{start_date}} {{end_date}} {{notice_date}} {{value}} {{billing_interval}} {{status}} {{app_name}}</span>
            </label>
            <textarea name="body" rows="10"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.85rem;font-family:monospace;box-sizing:border-box;resize:vertical;"><?php echo htmlspecialchars($tpl['body']); ?></textarea>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="font-size:.85rem;">
                <i class="ti ti-device-floppy"></i> <?php echo __('settings.mail_save'); ?>
            </button>
        </div>
    </form>
</div>
<?php endforeach; ?>