<div class="page-header">
    <h2>Benutzer</h2>
    <a href="/settings?tab=users&action=create" class="btn btn-primary"><i class="ti ti-plus"></i> Benutzer</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Rolle</th>
                    <th>Mandanten</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td style="font-weight:500"><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                        <span class="badge <?php echo $u['role'] === 'admin' ? 'badge-days-red' : 'badge-days-green'; ?>">
                            <?php echo ucfirst($u['role']); ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:var(--text-muted)"><?php echo htmlspecialchars($u['client_names'] ?? '–'); ?></td>
                    <td>
                        <span class="badge <?php echo $u['active'] ? 'badge-aktiv' : 'badge-abgelaufen'; ?>">
                            <?php echo $u['active'] ? 'Aktiv' : 'Inaktiv'; ?>
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="/settings?tab=users&action=edit&id=<?php echo $u['id']; ?>" class="btn btn-outline" style="padding:.3rem .6rem;"><i class="ti ti-edit"></i></a>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" action="/settings?tab=users" style="display:inline;" onsubmit="return confirm('Benutzer löschen?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-danger" style="padding:.3rem .6rem;"><i class="ti ti-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
