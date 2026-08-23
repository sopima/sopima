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
    <form id="pdf-form-<?php echo $type; ?>" method="POST" action="/settings?tab=pdf&action=save" enctype="multipart/form-data" style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1rem;">
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
        <input type="hidden" name="type" value="<?php echo $type; ?>">
        <input type="hidden" name="active" value="1">
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.pdf.title'); ?></label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($tpl['title']); ?>"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.pdf.body'); ?></label>
            <!-- Toolbar -->
            <div style="display:flex;gap:.3rem;margin-bottom:.3rem;flex-wrap:wrap;padding:.4rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-bottom:none;border-radius:6px 6px 0 0;">
                <button type="button" onclick="pdfFmt('<?php echo $type; ?>','bold')" style="padding:.2rem .55rem;font-weight:700;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">B</button>
                <button type="button" onclick="pdfFmt('<?php echo $type; ?>','italic')" style="padding:.2rem .55rem;font-style:italic;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">I</button>
                <button type="button" onclick="pdfFmt('<?php echo $type; ?>','underline')" style="padding:.2rem .55rem;text-decoration:underline;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">U</button>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <button type="button" onclick="pdfFmt('<?php echo $type; ?>','justifyLeft')" title="Linksbündig" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8676;</button>
                <button type="button" onclick="pdfFmt('<?php echo $type; ?>','justifyCenter')" title="Zentriert" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8596;</button>
                <button type="button" onclick="pdfFmt('<?php echo $type; ?>','justifyRight')" title="Rechtsbündig" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8677;</button>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <select onchange="pdfFmtSize('<?php echo $type; ?>',this.value);this.selectedIndex=0;" style="padding:.2rem .4rem;background:#2d3748;border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:3px;font-size:.8rem;">
                    <option value="">Größe</option>
                    <option value="1">Klein</option>
                    <option value="3">Normal</option>
                    <option value="5">Groß</option>
                    <option value="7">Sehr groß</option>
                </select>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <select onchange="pdfInsertPH('<?php echo $type; ?>',this.value);this.selectedIndex=0;" style="padding:.2rem .4rem;background:#2d3748;border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:3px;font-size:.8rem;">
                    <option value="">+ Platzhalter</option>
                    <optgroup label="Vertrag">
                        <option value="{{title}}">{{title}}</option>
                        <option value="{{partner}}">{{partner}}</option>
                        <option value="{{start_date}}">{{start_date}}</option>
                        <option value="{{end_date}}">{{end_date}}</option>
                        <option value="{{notice_date}}">{{notice_date}}</option>
                        <option value="{{value}}">{{value}}</option>
                        <option value="{{billing_interval}}">{{billing_interval}}</option>
                        <option value="{{status}}">{{status}}</option>
                    </optgroup>
                    <optgroup label="Mandant">
                        <option value="{{client_name}}">{{client_name}}</option>
                        <option value="{{client_street}}">{{client_street}}</option>
                        <option value="{{client_zip}}">{{client_zip}}</option>
                        <option value="{{client_city}}">{{client_city}}</option>
                        <option value="{{client_email}}">{{client_email}}</option>
                        <option value="{{client_phone}}">{{client_phone}}</option>
                    </optgroup>
                    <optgroup label="Kontakt">
                        <option value="{{contact_company}}">{{contact_company}}</option>
                        <option value="{{contact_first_name}}">{{contact_first_name}}</option>
                        <option value="{{contact_last_name}}">{{contact_last_name}}</option>
                        <option value="{{contact_street}}">{{contact_street}}</option>
                        <option value="{{contact_zip}}">{{contact_zip}}</option>
                        <option value="{{contact_city}}">{{contact_city}}</option>
                        <option value="{{contact_iban}}">{{contact_iban}}</option>
                        <option value="{{contact_bank}}">{{contact_bank}}</option>
                        <option value="{{contact_bic}}">{{contact_bic}}</option>
                    </optgroup>
                    <optgroup label="System">
                        <option value="{{app_name}}">{{app_name}}</option>
                    </optgroup>
                </select>
            </div>
            <!-- Editor -->
            <div id="pdf-editor-<?php echo $type; ?>"
                 contenteditable="true"
                 style="min-height:280px;background:#fff;color:#111;border:1px solid rgba(255,255,255,.1);border-radius:0 0 6px 6px;padding:.75rem;font-size:11pt;line-height:1.7;font-family:Arial,Helvetica,sans-serif;overflow-y:auto;"
            ><?php echo $tpl['body']; ?></div>
            <input type="hidden" name="body" id="pdf-body-<?php echo $type; ?>">
        </div>
        <div style="border-top:1px solid rgba(255,255,255,.08);padding-top:1rem;margin-top:.5rem;">
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.pdf.upload'); ?></label>
            <?php if (!empty($tpl['file_path']) && file_exists($tpl['file_path'])): ?>
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.75rem;">
                <span style="font-size:.83rem;color:#34d399;"><i class="ti ti-file-check"></i> <?php echo htmlspecialchars(basename($tpl['file_path'])); ?></span>
                <a href="/settings?tab=pdf&action=delete_file&client_id=<?php echo $client_id; ?>&type=<?php echo $type; ?>" style="font-size:.8rem;color:#f87171;"><?php echo __('settings.pdf.delete_file'); ?></a>
            </div>
            <?php endif; ?>
            <input type="file" name="pdf_file" accept="application/pdf"
                style="font-size:.85rem;color:var(--text-muted);">
            <p style="font-size:.78rem;color:var(--text-muted);margin:.4rem 0 0;"><?php echo __('settings.pdf.upload_hint'); ?></p>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="font-size:.85rem;">
                <i class="ti ti-device-floppy"></i> <?php echo __('settings.pdf.save'); ?>
            </button>
        </div>
    </form>
</div>
<?php endforeach; ?>
<script>
function pdfFmt(type, cmd) {
    document.getElementById("pdf-editor-" + type).focus();
    document.execCommand(cmd, false, null);
}
function pdfFmtSize(type, size) {
    document.getElementById("pdf-editor-" + type).focus();
    document.execCommand("fontSize", false, size);
}
function pdfInsertPH(type, ph) {
    if (!ph) return;
    document.getElementById("pdf-editor-" + type).focus();
    document.execCommand("insertText", false, ph);
}
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("form[id^='pdf-form-']").forEach(function(form) {
        var type = form.id.replace("pdf-form-", "");
        form.addEventListener("submit", function() {
            document.getElementById("pdf-body-" + type).value =
                document.getElementById("pdf-editor-" + type).innerHTML;
        });
    });
});
</script>
