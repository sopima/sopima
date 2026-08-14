<div class="page-header">
    <h2>Mandanten-Typen</h2>
    <a href="/clients" class="btn btn-outline"><i class="ti ti-arrow-left"></i> Zurück</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div class="card" style="padding:1.5rem;">
        <h3 style="font-size:.88rem;font-weight:600;color:rgba(255,255,255,.9);margin-bottom:1rem;">Neuer Typ</h3>
        <form method="POST" action="/clients">
            <input type="hidden" name="action" value="store_type">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="type_name" required placeholder="z.B. GmbH, Verband...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="ti ti-plus"></i> Hinzufügen</button>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><span>Vorhandene Typen</span></div>
        <?php if (empty($clientTypes)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);">Keine Typen vorhanden.</div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Name</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clientTypes as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td style="text-align:right;">
                            <form method="POST" action="/clients" style="display:inline;" onsubmit="return confirm('Typ löschen?')">
                                <input type="hidden" name="action" value="delete_type">
                                <input type="hidden" name="type_id" value="<?php echo $t['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem;"><i class="ti ti-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
