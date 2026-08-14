<div class="stat-grid">
    <div class="stat-card c-indigo">
        <div class="stat-glow"></div>
        <div class="stat-icon"><i class="ti ti-file-description"></i></div>
        <div class="stat-label">Verträge gesamt</div>
        <div class="stat-value"><?php echo $totalContracts; ?></div>
        <div class="stat-sub">alle Mandanten</div>
        <div class="stat-accent"></div>
    </div>
    <div class="stat-card success">
        <div class="stat-glow"></div>
        <div class="stat-icon"><i class="ti ti-check"></i></div>
        <div class="stat-label">Aktiv</div>
        <div class="stat-value"><?php echo $activeContracts; ?></div>
        <div class="stat-sub">€ <?php echo number_format($totalValue, 0, ',', '.'); ?> / Jahr</div>
        <div class="stat-accent"></div>
    </div>
    <div class="stat-card warn">
        <div class="stat-glow"></div>
        <div class="stat-icon"><i class="ti ti-clock"></i></div>
        <div class="stat-label">Läuft bald ab</div>
        <div class="stat-value"><?php echo $expiringSoon; ?></div>
        <div class="stat-sub">in 30 Tagen</div>
        <div class="stat-accent"></div>
    </div>
    <div class="stat-card danger">
        <div class="stat-glow"></div>
        <div class="stat-icon"><i class="ti ti-alert-triangle"></i></div>
        <div class="stat-label">Überfällig</div>
        <div class="stat-value"><?php echo $overdue; ?></div>
        <div class="stat-sub">Kündigung verpasst</div>
        <div class="stat-accent"></div>
    </div>
</div>

<div class="two-col">
    <div class="card">
        <div class="card-head">
            <span><i class="ti ti-alert-triangle" style="vertical-align:-2px;margin-right:4px;color:#fbbf24"></i>Fristen</span>
            <a href="/contracts">Alle →</a>
        </div>
        <?php if (empty($deadlines)): ?>
            <div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: .88rem;">Keine anstehenden Fristen.</div>
        <?php else: ?>
            <?php foreach ($deadlines as $d):
                $days = (int)$d['days_left'];
                $dotClass   = $days <= 7  ? 'dot-red'   : ($days <= 30 ? 'dot-amber' : 'dot-green');
                $badgeClass = $days <= 7  ? 'badge-days-red' : ($days <= 30 ? 'badge-days-amber' : 'badge-days-green');
            ?>
            <div class="alert-row">
                <div class="alert-dot <?php echo $dotClass; ?>"></div>
                <div class="alert-info">
                    <div class="alert-title"><?php echo htmlspecialchars($d['title']); ?></div>
                    <div class="alert-meta"><?php echo htmlspecialchars($d['client_name']); ?> · Kündigung bis <?php echo $d['notice_date']; ?></div>
                </div>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo $days; ?> Tage</span>
            </div></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head">
            <span><i class="ti ti-eye" style="vertical-align:-2px;margin-right:4px;color:#a5b4fc"></i>Zuletzt angesehen</span>
            <a href="/contracts?action=create">+ Vertrag</a>
        </div>
        <?php if (empty($recentContracts)): ?>
            <div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: .88rem;">Noch keine Verträge angesehen.</div>
        <?php else: ?>
            <?php foreach ($recentContracts as $c): ?>
            <a href="/contracts?action=view&id=<?php echo $c['id']; ?>" style="text-decoration:none;color:inherit;"><div class="contract-row" style="cursor:pointer;">
                <div class="contract-icon"><i class="ti ti-file-description" style="color:#a5b4fc;font-size:16px"></i></div>
                <div style="flex:1; min-width:0;">
                    <div class="contract-name"><?php echo htmlspecialchars($c['title']); ?></div>
                    <div class="contract-meta"><?php echo htmlspecialchars($c['client_name'] ?? '–'); ?> · <?php echo htmlspecialchars($c['category_name'] ?? '–'); ?></div>
                </div>
                <div>
                    <div class="contract-val"><?php echo $c['value'] ? '€ ' . number_format($c['value'], 0, ',', '.') : '–'; ?></div>
                    <div class="contract-interval"><?php echo $c['billing_interval'] ?? ''; ?></div>
                </div>
            </div></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top:1.5rem;">
    <div class="card-head">
        <span><i class="ti ti-coins" style="vertical-align:-2px;margin-right:4px;color:#34d399"></i>Monatliche Kosten</span>
    </div>
    <?php if (empty($costByClient)): ?>
        <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.88rem;">Keine aktiven Verträge mit laufenden Kosten.</div>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:1.5rem;padding:1.25rem 1.5rem;">
        <?php foreach ($costByClient as $cid => $data): ?>
            <div style="flex:1;min-width:220px;">
                <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);margin-bottom:.75rem;">
                    <?php echo htmlspecialchars($clientNames[$cid] ?? 'Mandant ' . $cid); ?>
                </div>
                <?php foreach (['ausgabe' => ['label' => 'Ausgaben', 'prefix' => '− € ', 'color' => '#f87171'], 'einnahme' => ['label' => 'Einnahmen', 'prefix' => '+ € ', 'color' => '#34d399']] as $dir => $cfg): ?>
                <?php if (empty($data[$dir]['rows'])) continue; ?>
                <div style="margin-bottom:1rem;">
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:.25rem;"><?php echo $cfg['label']; ?></div>
                    <div style="font-size:1.6rem;font-weight:700;color:<?php echo $cfg['color']; ?>;margin-bottom:.5rem;">
                        <?php echo $cfg['prefix'] . number_format($data[$dir]['total'], 2, ',', '.'); ?>
                        <span style="font-size:.82rem;font-weight:400;color:var(--text-muted);">/ Monat</span>
                    </div>
                    <?php $toggleId = 'costs-' . $cid . '-' . $dir; ?>
                    <div style="font-size:.78rem;color:var(--text-muted);cursor:pointer;margin-bottom:.25rem;user-select:none;"
                         onclick="var el=document.getElementById('<?php echo $toggleId; ?>');var arr=document.getElementById('arr-<?php echo $toggleId; ?>');el.style.display=el.style.display==='none'?'block':'none';arr.textContent=el.style.display==='none'?'▸':'▾';">
                        <span id="arr-<?php echo $toggleId; ?>">▸</span>
                        <?php echo count($data[$dir]['rows']); ?> Vertrag<?php echo count($data[$dir]['rows']) !== 1 ? 'ä' : ''; ?>
                    </div>
                    <div id="<?php echo $toggleId; ?>" style="display:none;">
                    <?php foreach ($data[$dir]['rows'] as $r): ?>
                    <div style="display:flex;justify-content:space-between;font-size:.83rem;padding:.2rem 0;border-bottom:1px solid rgba(255,255,255,.05);">
                        <span style="color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;">
                            <?php echo htmlspecialchars($r['title']); ?>
                        </span>
                        <span style="color:var(--text-primary);white-space:nowrap;margin-left:.5rem;">
                            € <?php echo number_format($r['monthly'], 2, ',', '.'); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

