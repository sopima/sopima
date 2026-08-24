<?php
$user = currentUser();
require BASE_PATH . '/app/Views/layouts/main.php';
?>
<div class="page-header">
    <h2><?php echo __('settings.title'); ?></h2>
</div>
<?php require BASE_PATH . '/app/Views/settings/tabs.php'; ?>

<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success" style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:8px;color:#34d399;font-size:.88rem;">
    Vorlage gespeichert.
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success" style="margin-bottom:1rem;padding:.75rem 1rem;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:8px;color:#34d399;font-size:.88rem;">
    Vorlage gelöscht.
</div>
<?php endif; ?>

<?php
$action = isset($template) ? '/settings/letter-templates/' . $template['id'] : '/settings/letter-templates';
$t = $template ?? [];
?>

<!-- Formular -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-head" style="padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.07);">
        <span style="font-weight:600;color:#fff;">
            <i class="ti ti-file-invoice" style="vertical-align:-2px;margin-right:6px;color:#a5b4fc;"></i>
            <?= isset($template) ? __('settings.letter.edit') : __('settings.letter.new') ?>
        </span>
    </div>
    <form method="POST" action="<?= $action ?>" style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.letter.name'); ?></label>
                <input type="text" name="name" required value="<?= htmlspecialchars($t['name'] ?? '') ?>"
                    style="width:100%;padding:.5rem .75rem;background:#1e2a3a;border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.letter.type'); ?></label>
                <select name="letter_type"
                    style="width:100%;padding:.5rem .75rem;background:#1e2a3a;border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
                    <?php foreach (['custom' => 'Sonstiges', 'kuendigung' => 'Kündigung', 'auskunft' => 'Vertragsauskunft', 'widerruf' => 'Widerruf'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($t['letter_type'] ?? 'custom') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.letter.subject'); ?></label>
            <input type="text" name="subject" value="<?= htmlspecialchars($t['subject'] ?? '') ?>"
                placeholder="z.B. Kündigung Vertrag {{contract_number}}"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.letter.body'); ?></label>
            <div style="display:flex;gap:.3rem;margin-bottom:.3rem;flex-wrap:wrap;padding:.4rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-bottom:none;border-radius:6px 6px 0 0;">
                <button type="button" onclick="ltrFmt('bold')" style="padding:.2rem .55rem;font-weight:700;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">B</button>
                <button type="button" onclick="ltrFmt('italic')" style="padding:.2rem .55rem;font-style:italic;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">I</button>
                <button type="button" onclick="ltrFmt('underline')" style="padding:.2rem .55rem;text-decoration:underline;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">U</button>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <button type="button" onclick="ltrFmt('justifyLeft')" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8676;</button>
                <button type="button" onclick="ltrFmt('justifyCenter')" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8596;</button>
                <button type="button" onclick="ltrFmt('justifyRight')" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8677;</button>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <select onchange="ltrFmtSize(this.value);this.selectedIndex=0;" style="padding:.2rem .4rem;background:#2d3748;border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:3px;font-size:.8rem;">
                    <option value="">Größe</option>
                    <option value="1">Klein</option>
                    <option value="3">Normal</option>
                    <option value="5">Groß</option>
                    <option value="7">Sehr groß</option>
                </select>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <select onchange="ltrInsertPH(this.value);this.selectedIndex=0;" style="padding:.2rem .4rem;background:#2d3748;border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:3px;font-size:.8rem;">
                    <option value="">+ Platzhalter</option>
                    <option value="{{contract_number}}">{{contract_number}}</option>
                    <option value="{{external_id}}">{{external_id}}</option>
                    <option value="{{partner_contract_number}}">{{partner_contract_number}}</option>
                    <option value="{{contract_ref}}">{{contract_ref}}</option>
                    <option value="{{partner}}">{{partner}}</option>
                    <option value="{{notice_date}}">{{notice_date}}</option>
                    <option value="{{start_date}}">{{start_date}}</option>
                    <option value="{{end_date}}">{{end_date}}</option>
                    <option value="{{monthly_cost}}">{{monthly_cost}}</option>
                    <option value="{{client_name}}">{{client_name}}</option>
                    <option value="{{client_address}}">{{client_address}}</option>
                    <option value="{{client_zip}}">{{client_zip}}</option>
                    <option value="{{client_city}}">{{client_city}}</option>
                    <option value="{{today}}">{{today}}</option>
                </select>
            </div>
            <div id="letter-editor"
                 contenteditable="true"
                 style="min-height:300px;background:#fff;color:#111;border:1px solid rgba(255,255,255,.1);border-radius:0 0 6px 6px;padding:.75rem;font-size:11pt;line-height:1.7;font-family:Arial,Helvetica,sans-serif;overflow-y:auto;"
            ><?= $t['body_html'] ?? '' ?></div>
            <input type="hidden" name="body_html" id="letter-body">
        </div>
        <div>
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;color:var(--text-muted);">
                <input type="checkbox" name="is_default" value="1" <?= !empty($t['is_default']) ? 'checked' : '' ?>>
                Als Standard markieren
            </label>
        </div>
        <div style="display:flex;gap:.5rem;">
            <button type="submit" class="btn btn-primary" style="font-size:.88rem;"><?php echo __('settings.letter.save'); ?></button>
            <?php if (isset($template)): ?>
                <a href="/settings/letter-templates" class="btn btn-outline" style="font-size:.88rem;"><?php echo __('settings.letter.cancel'); ?></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Vorhandene Vorlagen -->
<?php if (!empty($templates)): ?>
<div class="card">
    <div class="card-head" style="padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.07);">
        <span style="font-weight:600;color:#fff;">
            <i class="ti ti-list" style="vertical-align:-2px;margin-right:6px;color:#a5b4fc;"></i>
            Vorhandene Vorlagen
        </span>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,.07);">
                <th style="padding:.6rem 1.5rem;text-align:left;font-size:.78rem;color:var(--text-muted);font-weight:500;"><?php echo __('settings.letter.name'); ?></th>
                <th style="padding:.6rem 1rem;text-align:left;font-size:.78rem;color:var(--text-muted);font-weight:500;"><?php echo __('settings.letter.type'); ?></th>
                <th style="padding:.6rem 1rem;text-align:left;font-size:.78rem;color:var(--text-muted);font-weight:500;">Standard</th>
                <th style="padding:.6rem 1.5rem;text-align:right;font-size:.78rem;color:var(--text-muted);font-weight:500;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $tpl): ?>
            <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                <td style="padding:.75rem 1.5rem;font-size:.88rem;color:#fff;"><?= htmlspecialchars($tpl['name']) ?></td>
                <td style="padding:.75rem 1rem;font-size:.88rem;color:var(--text-muted);"><?= htmlspecialchars($tpl['letter_type']) ?></td>
                <td style="padding:.75rem 1rem;">
                    <?= $tpl['is_default'] ? '<i class="ti ti-check" style="color:#34d399;"></i>' : '' ?>
                </td>
                <td style="padding:.75rem 1.5rem;text-align:right;display:flex;gap:.5rem;justify-content:flex-end;">
                    <a href="/settings/letter-templates/<?= $tpl['id'] ?>" class="btn btn-outline" style="font-size:.8rem;padding:.3rem .75rem;"><?php echo __('settings.letter.edit'); ?></a>
                    <form method="POST" action="/settings/letter-templates/<?= $tpl['id'] ?>"
                          style="display:inline;"
                          onsubmit="return confirm(<?php echo json_encode(__("settings.letter.confirm_delete")); ?>);">
                        <input type="hidden" name="_delete" value="1">
                        <button type="submit" class="btn btn-danger" style="font-size:.8rem;padding:.3rem .75rem;"><?php echo __("settings.letter.delete"); ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function ltrFmt(cmd) {
    document.getElementById('letter-editor').focus();
    document.execCommand(cmd, false, null);
}
function ltrFmtSize(size) {
    document.getElementById('letter-editor').focus();
    document.execCommand('fontSize', false, size);
}
function ltrInsertPH(ph) {
    if (!ph) return;
    document.getElementById('letter-editor').focus();
    document.execCommand('insertText', false, ph);
}
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form[action^="/settings/letter-templates"]');
    if (form) {
        form.addEventListener('submit', function() {
            document.getElementById('letter-body').value =
                document.getElementById('letter-editor').innerHTML;
        });
    }
});
</script>
<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>