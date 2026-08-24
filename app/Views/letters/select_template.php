<?php
$user = currentUser();
require BASE_PATH . '/app/Views/layouts/main.php';
?>
<div class="page-header">
    <div class="page-header-content">
        <h2 class="page-title"><?php echo __('letter.title'); ?></h2>
        <p class="page-subtitle">Vertrag: <?= htmlspecialchars($contract['contract_number']) ?> – <?= htmlspecialchars($contract['partner']) ?></p>
    </div>
    <a href="/contracts?action=view&id=<?= $contract['id'] ?>" class="btn btn-outline">← Zurück</a>
</div>

<?php if (empty($templates)): ?>
    <div class="card" style="padding:2rem 1.5rem;color:var(--text-muted);">
        Keine Briefvorlagen vorhanden. <a href="/settings/letter-templates"><?php echo __('letter.create_now'); ?></a>
    </div>
<?php else: ?>
    <div class="card" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.07);">
                    <th style="padding:.6rem 1.5rem;text-align:left;font-size:.78rem;color:var(--text-muted);font-weight:500;">Vorlage</th>
                    <th style="padding:.6rem 1rem;text-align:left;font-size:.78rem;color:var(--text-muted);font-weight:500;">Typ</th>
                    <th style="padding:.6rem 1.5rem;text-align:right;font-size:.78rem;color:var(--text-muted);font-weight:500;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $tpl): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:.75rem 1.5rem;font-size:.88rem;color:#fff;">
                        <?= htmlspecialchars($tpl['name']) ?>
                        <?php if ($tpl['is_default']): ?>
                            <span style="margin-left:.5rem;font-size:.72rem;color:#a5b4fc;background:rgba(165,180,252,.1);padding:.1rem .4rem;border-radius:3px;">Standard</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:.75rem 1rem;font-size:.85rem;color:var(--text-muted);"><?= htmlspecialchars($tpl['letter_type']) ?></td>
                    <td style="padding:.75rem 1.5rem;text-align:right;display:flex;gap:.5rem;justify-content:flex-end;">
                        <button type="button"
                            onclick="openPreview(<?= $contract['id'] ?>, <?= $tpl['id'] ?>)"
                            class="btn btn-outline" style="font-size:.8rem;padding:.3rem .75rem;">
                            <i class="ti ti-eye"></i> Vorschau
                        </button>
                        <a href="/contracts/<?= $contract['id'] ?>/letter/<?= $tpl['id'] ?>/pdf"
                           class="btn btn-primary" style="font-size:.8rem;padding:.3rem .75rem;">
                            <i class="ti ti-download"></i> PDF
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- PDF.js Modal -->
<div id="pdf-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#1e2a3a;border-radius:10px;width:90vw;max-width:900px;height:88vh;display:flex;flex-direction:column;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.1);">
            <span style="font-weight:600;color:#fff;font-size:.95rem;"><i class="ti ti-file-text" style="margin-right:.4rem;color:#a5b4fc;"></i><?php echo __('letter.preview'); ?></span>
            <div style="display:flex;gap:.5rem;align-items:center;">
                <a id="pdf-download-btn" href="#" class="btn btn-primary" style="font-size:.8rem;padding:.3rem .75rem;">
                    <i class="ti ti-download"></i> PDF herunterladen
                </a>
                <button onclick="closePreview()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.4rem;line-height:1;">&times;</button>
            </div>
        </div>
        <div style="flex:1;overflow:hidden;">
            <canvas id="pdf-canvas" style="display:block;margin:0 auto;"></canvas>
            <div id="pdf-loading" style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);">
                <i class="ti ti-loader" style="margin-right:.5rem;"></i> Lade PDF...
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:center;gap:1rem;padding:.6rem;border-top:1px solid rgba(255,255,255,.07);">
            <button onclick="changePage(-1)" class="btn btn-outline" style="font-size:.8rem;padding:.3rem .75rem;">← Zurück</button>
            <span id="pdf-page-info" style="font-size:.85rem;color:var(--text-muted);"></span>
            <button onclick="changePage(1)" class="btn btn-outline" style="font-size:.8rem;padding:.3rem .75rem;">Weiter →</button>
        </div>
    </div>
</div>

<script src="/pdfjs/build/pdf.mjs" type="module"></script>
<script type="module">
import * as pdfjsLib from '/pdfjs/build/pdf.mjs';
pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdfjs/build/pdf.worker.mjs';

let pdfDoc = null;
let currentPage = 1;

window.openPreview = async function(contractId, templateId) {
    const modal = document.getElementById('pdf-modal');
    const canvas = document.getElementById('pdf-canvas');
    const loading = document.getElementById('pdf-loading');
    const downloadBtn = document.getElementById('pdf-download-btn');

    modal.style.display = 'flex';
    canvas.style.display = 'none';
    loading.style.display = 'flex';

    const url = `/contracts/${contractId}/letter/${templateId}/preview`;
    downloadBtn.href = `/contracts/${contractId}/letter/${templateId}/pdf`;

    try {
        pdfDoc = await pdfjsLib.getDocument(url).promise;
        currentPage = 1;
        await renderPage(currentPage);
        loading.style.display = 'none';
        canvas.style.display = 'block';
    } catch(e) {
        loading.innerHTML = '<span style="color:#f87171;">Fehler beim Laden des PDFs.</span>';
    }
};

async function renderPage(num) {
    const page = await pdfDoc.getPage(num);
    const canvas = document.getElementById('pdf-canvas');
    const container = canvas.parentElement;
    const viewport = page.getViewport({ scale: 1 });
    const scale = (container.clientWidth * 0.95) / viewport.width;
    const scaled = page.getViewport({ scale });

    canvas.width = scaled.width;
    canvas.height = scaled.height;
    canvas.style.maxHeight = (container.clientHeight - 10) + 'px';

    await page.render({ canvasContext: canvas.getContext('2d'), viewport: scaled }).promise;
    document.getElementById('pdf-page-info').textContent = `Seite ${num} von ${pdfDoc.numPages}`;
}

window.changePage = async function(delta) {
    if (!pdfDoc) return;
    const next = currentPage + delta;
    if (next < 1 || next > pdfDoc.numPages) return;
    currentPage = next;
    await renderPage(currentPage);
};

window.closePreview = function() {
    document.getElementById('pdf-modal').style.display = 'none';
    pdfDoc = null;
};

document.getElementById('pdf-modal').addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>