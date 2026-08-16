<div class="page-header">
    <h2><?php echo __('types.title'); ?></h2>
    <a href="/clients" class="btn btn-outline"><i class="ti ti-arrow-left"></i><?php echo __('types.back'); ?></a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div class="card" style="padding:1.5rem;">
        <h3 style="font-size:.88rem;font-weight:600;color:rgba(255,255,255,.9);margin-bottom:1rem;"><?php echo __('types.new'); ?></h3>
        <form method="POST" action="/clients">
            <input type="hidden" name="action" value="store_type">
            <div class="form-group">
                <label><?php echo __('types.name'); ?></label>
                <input type="text" name="type_name" required placeholder="z.B. GmbH, Verband...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="ti ti-plus"></i><?php echo __('types.add'); ?></button>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><span><?php echo __('types.existing'); ?></span></div>
        <?php if (empty($clientTypes)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);"><?php echo __('types.empty'); ?></div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th><?php echo __('types.col.name'); ?></th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clientTypes as $t): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($t['name']); ?></td>
                        <td style="text-align:right;">
                            <form method="POST" action="/clients" style="display:inline;" onsubmit="return confirm(this.dataset.confirm)" data-confirm="<?php echo __('types.confirm_delete'); ?>">
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
