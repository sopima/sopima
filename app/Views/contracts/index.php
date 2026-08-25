<?php $filter_client = $filter_client ?? 0; $filter_status = $filter_status ?? ''; $filter_search = $filter_search ?? ''; ?>
<div class="page-header">
    <h2><?php echo __('contracts.title'); ?></h2>
    <a href="/contracts?action=create" class="btn btn-primary"><i class="ti ti-plus"></i> <?php echo __('contracts.add'); ?></a>
</div>

<?php if (count($clients) > 1): ?>
<div class="tab-bar">
    <a href="/contracts" class="tab-item<?php echo $filter_client == 0 ? ' tab-active' : ''; ?>"><?php echo __('contracts.all'); ?></a>
    <?php foreach ($clients as $cl): ?>
        <a href="/contracts?client_id=<?php echo $cl['id']; ?><?php echo $filter_status ? '&status=' . urlencode($filter_status) : ''; ?><?php echo $filter_search ? '&q=' . urlencode($filter_search) : ''; ?>"
           class="tab-item<?php echo $filter_client == $cl['id'] ? ' tab-active' : ''; ?>">
            <?php echo htmlspecialchars($cl['name']); ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="filter-bar">
    <form method="GET" action="/contracts" style="display:flex;gap:1rem;align-items:flex-end;flex:1;flex-wrap:wrap;">
        <?php if ($filter_client): ?>
            <input type="hidden" name="client_id" value="<?php echo $filter_client; ?>">
        <?php endif; ?>
        <?php if (count($clients) <= 1): ?>
        <div class="form-group" style="margin:0;min-width:160px;flex:1;">
            <label><?php echo __('contracts.client'); ?></label>
            <select name="client_id">
                <option value=""><?php echo __('contracts.all'); ?></option>
                <?php foreach ($clients as $cl): ?>
                    <option value="<?php echo $cl['id']; ?>" <?php echo $filter_client == $cl['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cl['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group" style="margin:0;min-width:160px;flex:1;">
            <label><?php echo __('contracts.status'); ?></label>
            <select name="status">
                <option value=""><?php echo __('contracts.all'); ?></option>
                <option value="aktiv"      <?php echo $filter_status === 'aktiv'      ? 'selected' : ''; ?>><?php echo __('contracts.status.aktiv'); ?></option>
                <option value="gekuendigt" <?php echo $filter_status === 'gekuendigt' ? 'selected' : ''; ?>><?php echo __('contracts.status.gekuendigt'); ?></option>
                <option value="abgelaufen" <?php echo $filter_status === 'abgelaufen' ? 'selected' : ''; ?>><?php echo __('contracts.status.abgelaufen'); ?></option>
                <option value="pausiert"   <?php echo $filter_status === 'pausiert'   ? 'selected' : ''; ?>><?php echo __('contracts.status.pausiert'); ?></option>
            </select>
        </div>
        <div class="form-group" style="margin:0;min-width:200px;flex:2;">
            <label><?php echo __('contracts.search'); ?></label>
            <input type="text" name="q" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="<?php echo __('contracts.search_placeholder'); ?>">
        </div>
        <button type="submit" class="btn btn-outline"><i class="ti ti-filter"></i> <?php echo __('contracts.filter'); ?></button>
        <a href="/contracts" class="btn btn-outline"><i class="ti ti-x"></i> <?php echo __('contracts.reset'); ?></a>
    </form>
</div>

<div class="card">
    <?php if (empty($contracts)): ?>
        <div style="padding:2rem;text-align:center;color:var(--text-muted);">
            <i class="ti ti-file-off" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4;"></i>
            <?php echo __('contracts.empty'); ?>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th><?php echo __('contracts.col.title'); ?></th>
                    <th><?php echo __('contracts.col.partner'); ?></th>
                    <th><?php echo __('contracts.col.client'); ?></th>
                    <th><?php echo __('contracts.col.category'); ?></th>
                    <th><?php echo __('contracts.col.status'); ?></th>
                    <th><?php echo __('contracts.col.value'); ?></th>
                    <th><?php echo __('contracts.col.notice_date'); ?></th>
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
                                $ampel_title = __('contracts.status.' . $c["status"]);
                            } elseif (!empty($c["notice_date"])) {
                                $days = (int)((strtotime($c["notice_date"]) - time()) / 86400);
                                if ($days < 0)       { $ampel_class = "ampel-red";   $ampel_title = __('contracts.ampel.overdue') . " (" . abs($days) . " " . __('contracts.ampel.days') . ")"; }
                                elseif ($days <= 60) { $ampel_class = "ampel-amber"; $ampel_title = __('contracts.ampel.soon') . " " . $days . " " . __('contracts.ampel.days'); }
                                else                 { $ampel_class = "ampel-green"; $ampel_title = __('contracts.ampel.soon') . " " . $days . " " . __('contracts.ampel.days'); }
                            } elseif ($c["status"] === "aktiv") {
                                $ampel_class = "ampel-green";
                                $ampel_title = __('contracts.ampel.active');
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
                    <td><span class="badge badge-<?php echo $c['status']; ?>"><?php echo __('contracts.status.' . $c['status']); ?></span></td>
                    <td><?php echo $c['value'] ? '€ ' . number_format($c['value'], 2, ',', '.') : '–'; ?></td>
                    <td><?php echo $c['notice_date'] ? date('d.m.Y', strtotime($c['notice_date'])) : '–'; ?></td>
                    <td style="white-space:nowrap;">
                        <a href="/contracts?action=view&id=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding:.3rem .6rem;" title="<?php echo __('contracts.details'); ?>"><i class="ti ti-file-text"></i></a>
                        <a href="/contracts?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-outline" style="padding:.3rem .6rem;" title="<?php echo __('contracts.edit'); ?>"><i class="ti ti-pencil"></i></a>
                        <form method="POST" action="/contracts" style="display:inline;" onsubmit="return confirm('<?php echo __('contracts.confirm_delete'); ?>')">
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
