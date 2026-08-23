<?php
$saved = $_GET['saved'] ?? '';
$types_label = [
    'datenschutz' => __('settings.pdf.type_datenschutz'),
    'agb'         => __('settings.pdf.type_agb'),
    'vertrag'     => __('settings.pdf.type_vertrag'),
];
?>
<?php if ($saved === '1'): ?>
<div class="alert alert-success" style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:8px;color:#34d399;font-size:.88rem;">
    <?php echo __('settings.pdf.saved'); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;">
    <label style="font-size:.85rem;color:var(--text-muted);"><?php echo __('settings.pdf.mandant'); ?>:</label>
    <div class="form-group" style="margin:0;">
    <select onchange="location.href='/settings?tab=pdf&client_id='+this.value">
        <?php foreach ($clients as $c): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo $c['id'] == $client_id ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    </div>
</div>

<?php foreach ($templates as $type => $tpl): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-head">
        <span><i class="ti ti-file-text" style="vertical-align:-2px;margin-right:4px;color:#a5b4fc"></i>
        <?php echo $types_label[$type]; ?></span>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.83rem;color:var(--text-muted);">
            <input type="checkbox" form="pdf-form-<?php echo $type; ?>" name="attach" value="1" <?php echo $tpl['attach'] ? 'checked' : ''; ?>>
            <?php echo __('settings.pdf.attach'); ?>
        </label>
    </div>
    <form id="pdf-form-<?php echo $type; ?>" method="POST" action="/settings?tab=pdf&action=save" style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1rem;">
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <input type="hidden" name="active" value="1">
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.pdf.title'); ?></label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($tpl['title']); ?>"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;">
                <?php echo __('settings.pdf.body'); ?>
                <span style="margin-left:.75rem;opacity:.6;"><?php echo __('settings.pdf.placeholders'); ?>: {{title}} {{partner}} {{start_date}} {{end_date}} {{value}} {{client_name}} {{client_street}} {{client_zip}} {{client_city}} {{client_email}} {{contact_company}} {{contact_first_name}} {{contact_last_name}} {{contact_street}} {{contact_zip}} {{contact_city}} {{contact_iban}} {{contact_bank}} {{contact_bic}}</span>
            </label>
            <textarea name="body" rows="14"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.85rem;font-family:monospace;box-sizing:border-box;resize:vertical;"><?php echo htmlspecialchars($tpl['body']); ?></textarea>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="font-size:.85rem;">
                <i class="ti ti-device-floppy"></i> <?php echo __('settings.pdf.save'); ?>
            </button>
        </div>
    </form>
</div>
<?php endforeach; ?>
