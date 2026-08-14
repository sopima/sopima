<?php $filter_client = $filter_client ?? 0; $filter_status = $filter_status ?? ''; $filter_search = $filter_search ?? ''; ?>
<div class="page-header">
    <h2>Verträge</h2>
    <a href="/contracts?action=create" class="btn btn-primary"><i class="ti ti-plus"></i> Vertrag</a>
</div>

<div class="filter-bar">
    <form method="GET" action="/contracts" style="display:flex;gap:1rem;align-items:flex-end;flex:1;flex-wrap:wrap;">
        <div class="form-group" style="margin:0;min-width:160px;flex:1;">
            <label>Mandant</label>
            <select name="client_id">
                <option value="">Alle</option>
                <?php foreach ($clients as $cl): ?>
                    <option value="<?php echo $cl['id']; ?>" <?php echo $filter_client == $cl['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cl['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:160px;flex:1;">
            <label>Status</label>
            <select name="status">
                <option value="">Alle</option>
                <option value="aktiv"      <?php echo $filter_status === 'aktiv'      ? 'selected' : ''; ?>>Aktiv</option>
                <option value="gekuendigt" <?php echo $filter_status === 'gekuendigt' ? 'selected' : ''; ?>>Gekündigt</option>
                <option value="abgelaufen" <?php echo $filter_status === 'abgelaufen' ? 'selected' : ''; ?>>Abgelaufen</option>
                <option value="pausiert"   <?php echo $filter_status === 'pausiert'   ? 'selected' : ''; ?>>Pausiert</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:200px;flex:2;">
            <label>Suche</label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="Titel, Partner, Notizen…">
        </div>
        <button type="submit" class="btn btn-outline"><i class="ti ti-filter"></i> Filtern</button>
        <a href="/contracts" class="btn btn-outline"><i class="ti ti-x"></i> Zurücksetzen</a>
    </form>
</div>

<div class="card">
    <?php if (empty($contracts)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">
            <i class="ti ti-file-off" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
            Keine Verträge gefunden.
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Partner</th>
                    <th>Mandant</th>
                    <th>Kategorie</th>
                    <th>Status</th>
                    <th>Wert</th>
                    <th>Laufzeit bis</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contracts as $c): ?>
                <tr>
                    <td style="font-weight:500;">
                        <?php
                            $ampel_class = "ampel-grey";
                            $ampel_title = "";
                            if (in_array($c["status"], ["abgelaufen","gekuendigt"])) {
                                $ampel_class = "ampel-red";
                                $ampel_title = ucfirst($c["status"]);
                            } elseif (!empty($c["notice_date"])) {
                                $days = (int)((strtotime($c["notice_date"]) - time()) / 86400);
                                if ($days < 0)       { $ampel_class = "ampel-red";   $ampel_title = "Kündigung überfällig (" . abs($days) . " Tage)"; }
                                elseif ($days <= 60) { $ampel_class = "ampel-amber"; $ampel_title = "Kündigung in " . $days . " Tagen"; }
                                else                 { $ampel_class = "ampel-green"; $ampel_title = "Kündigung in " . $days . " Tagen"; }
                            } elseif ($c["status"] === "aktiv") {
                                $ampel_class = "ampel-green";
                                $ampel_title = "Aktiv";
                            }
                        ?>
                        <span class="ampel <?php echo $ampel_class; ?>" title="<?php echo htmlspecialchars($ampel_title); ?>"></span>
                        <?php echo htmlspecialchars($c["title"]); ?>
                    </td>
                    <td><?php echo htmlspecialchars($c['partner'] ?? '–'); ?></td>
                    <td><?php echo htmlspecialchars($c['client_name'] ?? '–'); ?></td>
                    <td>
                        <?php if ($c['category_name']): ?>
                            <span class="badge" style="background:<?php echo $c['category_color']; ?>22;color:<?php echo $c['category_color']; ?>;border:1px solid <?php echo $c['category_color']; ?>44">
                                <?php echo htmlspecialchars($c['category_name']); ?>
                            </span>
                        <?php else: ?>–<?php endif; ?>
                    </td>
                    <td><span class="badge badge-<?php echo $c['status']; ?>"><?php echo ucfirst($c['status']); ?></span></td>
                    <td><?php echo $c['value'] ? '€ ' . number_format($c['value'], 2, ',', '.') : '–'; ?></td>
                    <td><?php echo $c['notice_date'] ? date('d.m.Y', strtotime($c['notice_date'])) : '–'; ?></td>
                    <td style="white-space:nowrap;">
                        <a href="/contracts?action=view&id=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding:.3rem .6rem;" title="Details"><i class="ti ti-file-text"></i></a>
                        <a href="/contracts?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding:.3rem .6rem;" title="Bearbeiten"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="/contracts" style="display:inline;" onsubmit="return confirm('Vertrag löschen?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
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
