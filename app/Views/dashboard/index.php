<div class="tab-bar">
    <a href="?tab=0" class="tab-item <?php echo $activeTab === 0 ? 'tab-active' : ''; ?>"><?php echo __('dashboard.all_clients'); ?></a>
    <?php foreach ($tabs as $cid => $t): ?>
    <a href="?tab=<?php echo $cid; ?>" class="tab-item <?php echo $activeTab === $cid ? 'tab-active' : ''; ?>">
        <?php echo htmlspecialchars($t['name']); ?>
    </a>
    <?php endforeach; ?>
</div>

<?php
$showTotal      = $activeTab === 0 ? $totalContracts : ($tabs[$activeTab]['totalContracts'] ?? 0);
$showExpiring   = $activeTab === 0 ? $expiringSoon   : ($tabs[$activeTab]['expiringSoon']   ?? 0);
$showOverdue    = $activeTab === 0 ? $overdue        : ($tabs[$activeTab]['overdue']         ?? 0);
$showExpenses   = $activeTab === 0 ? $totalExpenses  : ($tabs[$activeTab]['totalExpenses']   ?? 0);
$showDeadlines  = $activeTab === 0 ? $deadlines      : ($tabs[$activeTab]['deadlines']       ?? []);
?>

<div class="dash-stat-row">
    <div class="stat-card c-indigo">
        <div class="stat-label"><?php echo __('dashboard.contracts_total'); ?></div>
        <div class="stat-value"><?php echo $showTotal; ?></div>
        <div class="stat-sub"><?php echo $activeTab === 0 ? __('dashboard.all_clients') : htmlspecialchars($tabs[$activeTab]['name'] ?? ''); ?></div>
    </div>
    <div class="stat-card warn">
        <div class="stat-label"><?php echo __('dashboard.expiring_soon'); ?></div>
        <div class="stat-value"><?php echo $showExpiring; ?></div>
        <div class="stat-sub"><?php echo __('dashboard.in_30_days'); ?></div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label"><?php echo __('dashboard.overdue'); ?></div>
        <div class="stat-value"><?php echo $showOverdue; ?></div>
        <div class="stat-sub"><?php echo __('dashboard.missed_cancellation'); ?></div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label"><?php echo __('dashboard.expenses'); ?></div>
        <div class="stat-value">€ <?php echo number_format($showExpenses, 0, ',', '.'); ?></div>
        <div class="stat-sub"><?php echo __('dashboard.per_month'); ?></div>
    </div>
</div>

<div class="card" style="margin-top:1.25rem;">
    <div class="card-head">
        <span><?php echo __('dashboard.deadlines'); ?></span>
        <a href="/contracts<?php echo $activeTab !== 0 ? '?client_id='.$activeTab : ''; ?>"><?php echo __('dashboard.all'); ?> →</a>
    </div>
    <?php if (empty($showDeadlines)): ?>
        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.88rem;"><?php echo __('dashboard.no_deadlines'); ?></div>
    <?php else: ?>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th><?php echo __('dashboard.contract'); ?></th>
                <th><?php echo __('dashboard.partner'); ?></th>
                <th><?php echo __('dashboard.cancellation_until'); ?></th>
                <th><?php echo __('dashboard.status'); ?></th>
                <th style="text-align:right"><?php echo __('dashboard.costs_per_month'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($showDeadlines as $d):
            $days = (int)$d['days_left'];
            if ($days <= 7) { $badgeClass = 'badge-days-red'; $label = __('dashboard.overdue'); }
            elseif ($days <= 30) { $badgeClass = 'badge-days-amber'; $label = __('dashboard.expiring_soon'); }
            else { $badgeClass = 'badge-days-green'; $label = __('dashboard.active'); }
            $monthly = null;
            if ($d['value'] && $d['billing_interval']) {
                $monthly = $d['billing_interval'] === 'jaehrlich' ? $d['value'] / 12 : $d['value'];
            }
        ?>
        <tr style="cursor:pointer;" onclick="window.location='/contracts?action=view&id=<?php echo $d['id']; ?>'">
            <td><strong><?php echo htmlspecialchars($d['title']); ?></strong></td>
            <td style="color:var(--text-muted)"><?php echo htmlspecialchars($d['partner_name'] ?? '–'); ?></td>
            <td><?php echo $d['cancellation_deadline'] ? date('d.m.Y', strtotime($d['cancellation_deadline'])) : '–'; ?></td>
            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $label; ?></span></td>
            <td style="text-align:right"><?php echo $monthly ? '€ ' . number_format($monthly, 2, ',', '.') : '–'; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<div class="two-col" style="margin-top:1rem;">
    <div class="card">
        <div class="card-head">
            <span><?php echo __('dashboard.recently_viewed'); ?></span>
            <a href="/contracts?action=create">+ <?php echo __('dashboard.add_contract'); ?></a>
        </div>
        <?php if (empty($recentContracts)): ?>
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.88rem;"><?php echo __('dashboard.no_recent'); ?></div>
        <?php else: ?>
            <?php foreach ($recentContracts as $c): ?>
            <a href="/contracts?action=view&id=<?php echo $c['id']; ?>" style="text-decoration:none;color:inherit;">
            <div class="contract-row">
                <div class="contract-icon"><i class="ti ti-file-description" style="color:var(--accent);font-size:15px"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="contract-name"><?php echo htmlspecialchars($c['title']); ?></div>
                    <div class="contract-meta"><?php echo htmlspecialchars($c['client_name'] ?? '–'); ?> · <?php echo htmlspecialchars($c['category_name'] ?? '–'); ?></div>
                </div>
                <div>
                    <div class="contract-val"><?php echo $c['value'] ? '€ ' . number_format($c['value'], 0, ',', '.') : '–'; ?></div>
                    <div class="contract-interval"><?php echo $c['billing_interval'] ?? ''; ?></div>
                </div>
            </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <span><?php echo __('dashboard.monthly_costs'); ?></span>
        </div>
        <div style="padding:1rem 0;display:grid;grid-template-columns:1fr 1fr;">
            <div style="text-align:center;padding:1rem;border-right:1px solid var(--border);">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.25rem"><?php echo __('dashboard.expenses'); ?></div>
                <div style="font-size:1.5rem;font-weight:600;color:var(--danger)">− € <?php echo number_format($showExpenses, 2, ',', '.'); ?></div>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px"><?php echo $activeTab === 0 ? $activeExpenses : ($tabs[$activeTab]['activeExpenses'] ?? 0); ?> <?php echo __('dashboard.contracts_count_pl'); ?></div>
            </div>
            <?php $showIncome = $activeTab === 0 ? $totalIncome : ($tabs[$activeTab]['totalIncome'] ?? 0); ?>
            <?php if (true): ?>
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.25rem"><?php echo __('dashboard.income'); ?></div>
                <div style="font-size:1.5rem;font-weight:600;color:var(--success)">+ € <?php echo number_format($showIncome, 2, ',', '.'); ?></div>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px"><?php echo $activeTab === 0 ? $activeIncome : ($tabs[$activeTab]['activeIncome'] ?? 0); ?> <?php echo __('dashboard.contracts_count_pl'); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>