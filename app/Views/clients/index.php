<div class="page-header">
    <h2>Mandanten</h2>
    <a href="/clients?action=create" class="btn btn-primary"><i class="ti ti-plus"></i> Mandant</a>
</div>

<div class="card">
    <?php if (empty($clients)): ?>
        <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
            <i class="ti ti-users-off" style="font-size: 2rem; display: block; margin-bottom: .5rem; opacity: .4;"></i>
            Keine Mandanten gefunden.
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Typ</th>
                    <th>Verträge</th>
                    <th>Status</th>
                    <th>Beschreibung</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $cl): ?>
                <tr>
                    <td style="font-weight:500"><?php echo htmlspecialchars($cl['name']); ?></td>
                    <td><?php echo ucfirst($cl['type']); ?></td>
                    <td><?php echo $cl['contract_count']; ?></td>
                    <td>
                        <span class="badge <?php echo $cl['active'] ? 'badge-aktiv' : 'badge-abgelaufen'; ?>">
                            <?php echo $cl['active'] ? 'Aktiv' : 'Inaktiv'; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($cl['description'] ?? '–'); ?></td>
                    <td style="white-space:nowrap;">
                        <a href="/clients?action=edit&id=<?php echo $cl['id']; ?>" class="btn btn-outline" style="padding:.3rem .6rem;"><i class="ti ti-edit"></i></a>
                        <?php if ($cl['contract_count'] == 0): ?>
                        <form method="POST" action="/clients" style="display:inline;" onsubmit="return confirm('Mandant deaktivieren?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $cl['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem;"><i class="ti ti-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
