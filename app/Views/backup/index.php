<div class="page-header">
    <h2>Einstellungen</h2>
</div>

<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
    <a href="/settings?tab=users" class="btn btn-outline">
        <i class="ti ti-users"></i> Benutzer
    </a>
    <a href="/settings?tab=tokens" class="btn btn-outline">
        <i class="ti ti-key"></i> API-Tokens
    </a>
    <a href="/backup" class="btn btn-primary">
        <i class="ti ti-database"></i> Datensicherung
    </a>
</div>

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
            <h3 class="card-title"><i class="ti ti-download" style="margin-right:.4rem;opacity:.7;"></i>Daten exportieren</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                Exportiert alle Verträge, Notizen, Kontakte und Felder dieses Mandanten.
                Dokument-Dateien und API-Tokens sind nicht enthalten.
            </p>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <form method="POST" action="/backup?action=export-json">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-file-type-json"></i> JSON exportieren
                    </button>
                </form>
                <form method="POST" action="/backup?action=export-csv">
                    <button type="submit" class="btn btn-outline">
                        <i class="ti ti-file-zip"></i> CSV exportieren (ZIP)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Import -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-upload" style="margin-right:.4rem;opacity:.7;"></i>Daten importieren</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning mb-4" style="display:flex;gap:.75rem;align-items:flex-start;">
                <i class="ti ti-alert-triangle" style="font-size:1.2rem;flex-shrink:0;margin-top:.1rem;"></i>
                <div>
                    <strong>Achtung:</strong> Alle bestehenden Verträge dieses Mandanten werden gelöscht
                    und durch den gesicherten Stand ersetzt. Dieser Vorgang kann nicht rückgängig gemacht werden.
                </div>
            </div>
            <form method="POST" action="/backup?action=import" enctype="multipart/form-data">
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label" style="display:block;margin-bottom:.5rem;">Backup-Datei (JSON)</label>
                    <label id="file-drop" style="
                        display:flex;flex-direction:column;align-items:center;justify-content:center;
                        gap:.5rem;padding:2rem 1.5rem;border-radius:.75rem;cursor:pointer;
                        border:2px dashed rgba(255,255,255,.15);
                        background:rgba(255,255,255,.04);
                        transition:border-color .2s,background .2s;
                    " onmouseover="this.style.borderColor='rgba(255,255,255,.3)';this.style.background='rgba(255,255,255,.07)'"
                       onmouseout="this.style.borderColor='rgba(255,255,255,.15)';this.style.background='rgba(255,255,255,.04)'">
                        <i class="ti ti-file-import" style="font-size:2rem;opacity:.5;"></i>
                        <span id="file-label" style="font-size:.9rem;color:var(--text-muted);">Datei auswählen oder hierher ziehen</span>
                        <span style="font-size:.78rem;opacity:.45;">.json</span>
                        <input type="file" name="backup_file" accept=".json" required
                               style="position:absolute;width:1px;height:1px;opacity:0;"
                               onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Datei auswählen';">
                    </label>
                    <p style="font-size:.8rem;color:var(--text-muted);margin-top:.5rem;">
                        Nur <?php echo APP_NAME; ?> JSON-Backups. Nach dem Import müssen API-Tokens neu erstellt werden.
                    </p>
                </div>
                <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Wirklich importieren? Alle bestehenden Verträge werden unwiderruflich ersetzt.')">
                    <i class="ti ti-upload"></i> Import starten
                </button>
            </form>
        </div>
    </div>

</div>