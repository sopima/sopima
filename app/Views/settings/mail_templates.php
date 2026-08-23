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
<div class="card" style="margin-bottom:1rem;">
    <div class="card-head" style="cursor:pointer;" onclick="toggleMail(<?php echo $tpl['id']; ?>)">
        <span>
            <i class="ti ti-chevron-right" id="mail-chevron-<?php echo $tpl['id']; ?>" style="vertical-align:-2px;margin-right:4px;transition:transform .2s;"></i>
            <i class="ti ti-mail" style="vertical-align:-2px;margin-right:4px;color:#a5b4fc"></i>
            <?php echo htmlspecialchars($tpl['event']); ?>
        </span>
        <label style="display:flex;align-items:center;gap:.4rem;font-size:.83rem;color:var(--text-muted);" onclick="event.stopPropagation()">
            <input type="checkbox" form="form-<?php echo $tpl['id']; ?>" name="active" value="1" <?php echo $tpl['active'] ? 'checked' : ''; ?>>
            <?php echo __('settings.mail_active'); ?>
        </label>
    </div>
    <div id="mail-wrap-<?php echo $tpl['id']; ?>" style="display:none;">
    <form id="form-<?php echo $tpl['id']; ?>" method="POST" action="/settings?tab=mail&action=save" style="padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:1rem;">
        <input type="hidden" name="id" value="<?php echo $tpl['id']; ?>">
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.mail_subject'); ?></label>
            <input type="text" name="subject" value="<?php echo htmlspecialchars($tpl['subject']); ?>"
                style="width:100%;padding:.5rem .75rem;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-muted);display:block;margin-bottom:.35rem;"><?php echo __('settings.mail_body'); ?></label>
            <div style="display:flex;gap:.3rem;margin-bottom:.3rem;flex-wrap:wrap;padding:.4rem;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-bottom:none;border-radius:6px 6px 0 0;">
                <button type="button" onclick="mailFmt(<?php echo $tpl['id']; ?>,'bold')" style="padding:.2rem .55rem;font-weight:700;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">B</button>
                <button type="button" onclick="mailFmt(<?php echo $tpl['id']; ?>,'italic')" style="padding:.2rem .55rem;font-style:italic;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">I</button>
                <button type="button" onclick="mailFmt(<?php echo $tpl['id']; ?>,'underline')" style="padding:.2rem .55rem;text-decoration:underline;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">U</button>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <button type="button" onclick="mailFmt(<?php echo $tpl['id']; ?>,'justifyLeft')" title="Linksbündig" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8676;</button>
                <button type="button" onclick="mailFmt(<?php echo $tpl['id']; ?>,'justifyCenter')" title="Zentriert" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8596;</button>
                <button type="button" onclick="mailFmt(<?php echo $tpl['id']; ?>,'justifyRight')" title="Rechtsbündig" style="padding:.2rem .55rem;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.07);color:#fff;border-radius:3px;cursor:pointer">&#8677;</button>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <select onchange="mailFmtSize(<?php echo $tpl['id']; ?>,this.value);this.selectedIndex=0;" style="padding:.2rem .4rem;background:#2d3748;border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:3px;font-size:.8rem;">
                    <option value="">Größe</option>
                    <option value="1">Klein</option>
                    <option value="3">Normal</option>
                    <option value="5">Groß</option>
                    <option value="7">Sehr groß</option>
                </select>
                <span style="border-left:1px solid rgba(255,255,255,.15);margin:0 .2rem"></span>
                <select onchange="mailInsertPH(<?php echo $tpl['id']; ?>,this.value);this.selectedIndex=0;" style="padding:.2rem .4rem;background:#2d3748;border:1px solid rgba(255,255,255,.15);color:#fff;border-radius:3px;font-size:.8rem;">
                    <option value="">+ Platzhalter</option>
                    <option value="{{title}}">{{title}}</option>
                    <option value="{{partner}}">{{partner}}</option>
                    <option value="{{start_date}}">{{start_date}}</option>
                    <option value="{{end_date}}">{{end_date}}</option>
                    <option value="{{notice_date}}">{{notice_date}}</option>
                    <option value="{{value}}">{{value}}</option>
                    <option value="{{billing_interval}}">{{billing_interval}}</option>
                    <option value="{{status}}">{{status}}</option>
                    <option value="{{app_name}}">{{app_name}}</option>
                </select>
            </div>
            <div id="mail-editor-<?php echo $tpl['id']; ?>"
                 contenteditable="true"
                 style="min-height:220px;background:#fff;color:#111;border:1px solid rgba(255,255,255,.1);border-radius:0 0 6px 6px;padding:.75rem;font-size:11pt;line-height:1.7;font-family:Arial,Helvetica,sans-serif;overflow-y:auto;"
            ><?php echo nl2br(htmlspecialchars($tpl['body'])); ?></div>
            <input type="hidden" name="body" id="mail-body-<?php echo $tpl['id']; ?>">
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="font-size:.85rem;">
                <i class="ti ti-device-floppy"></i> <?php echo __('settings.mail_save'); ?>
            </button>
        </div>
    </form>
    </div>
</div>
<?php endforeach; ?>
<script>
function toggleMail(id) {
    var wrap = document.getElementById("mail-wrap-" + id);
    var chev = document.getElementById("mail-chevron-" + id);
    var open = wrap.style.display === "block";
    wrap.style.display = open ? "none" : "block";
    chev.style.transform = open ? "" : "rotate(90deg)";
}
function mailFmt(id, cmd) {
    document.getElementById("mail-editor-" + id).focus();
    document.execCommand(cmd, false, null);
}
function mailFmtSize(id, size) {
    document.getElementById("mail-editor-" + id).focus();
    document.execCommand("fontSize", false, size);
}
function mailInsertPH(id, ph) {
    if (!ph) return;
    document.getElementById("mail-editor-" + id).focus();
    document.execCommand("insertText", false, ph);
}
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll("form[id^='form-']").forEach(function(form) {
        var id = form.id.replace("form-", "");
        form.addEventListener("submit", function() {
            document.getElementById("mail-body-" + id).value =
                document.getElementById("mail-editor-" + id).innerHTML;
        });
    });
});
</script>