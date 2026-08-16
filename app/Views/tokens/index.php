<div class="page-header">
    <h2><?php echo __('tokens.title'); ?></h2>
    <a href="/settings?tab=tokens&action=create" class="btn btn-primary"><i class="ti ti-plus"></i><?php echo __('tokens.add'); ?></a>
</div>

<div class="card">
    <?php if (empty($tokens)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">
            <i class="ti ti-key-off" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
            <?php echo __('tokens.empty'); ?>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><?php echo __('tokens.col.name'); ?></th>
                    <th><?php echo __('tokens.col.client'); ?></th>
                    <th><?php echo __('tokens.col.perms'); ?></th>
                    <th><?php echo __('tokens.col.status'); ?></th>
                    <th><?php echo __('tokens.col.last_used'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tokens as $t): ?>
                <tr>
                    <td style="font-weight:500"><?php echo htmlspecialchars($t['name']); ?></td>
                    <td><?php echo htmlspecialchars($t['client_name'] ?? __('tokens.all_clients')); ?></td>
                    <td style="font-size:.78rem;">
                        <?php foreach (json_decode($t['permissions'], true) as $p): ?>
                            <span class="badge badge-days-green" style="margin-right:.2rem;"><?php echo $p; ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $t['active'] ? 'badge-aktiv' : 'badge-abgelaufen'; ?>">
                            <?php echo $t['active'] ? __('tokens.active') : __('tokens.inactive'); ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:var(--text-muted)"><?php echo $t['last_used_at'] ?? '–'; ?></td>
                    <td style="white-space:nowrap;">
                        <form method="POST" action="/settings?tab=tokens" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="btn btn-outline" style="padding:.3rem .6rem;" title="<?php echo $t['active'] ? __('tokens.deactivate') : __('tokens.activate'); ?>">
                                <i class="ti ti-<?php echo $t['active'] ? 'pause' : 'play'; ?>"></i>
                            </button>
                        </form>
                        <form method="POST" action="/settings?tab=tokens" style="display:inline;" onsubmit="return confirm(this.dataset.confirm)" data-confirm="<?php echo __('tokens.confirm_delete'); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
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
