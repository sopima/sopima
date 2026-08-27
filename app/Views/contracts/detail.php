<?php
$statusColors = ['aktiv'=>'#4ade80','gekuendigt'=>'#f87171','abgelaufen'=>'#94a3b8','pausiert'=>'#fbbf24'];
$color = $statusColors[$contract['status']] ?? '#94a3b8';
$uploadError = $_GET['upload_error'] ?? '';
$errorMsg = ['1'=>__('cf.doc.err.1'),'2'=>__('cf.doc.err.2'),'3'=>__('cf.doc.err.3')];
$activeTab = $_GET['tab'] ?? 'uebersicht';
?>

<div class="page-header">
    <h2><?php echo htmlspecialchars($contract['title']); ?></h2>
    <div style="display:flex;gap:.5rem;">
        <a href="/contracts/<?php echo $contract['id']; ?>/letter" class="btn btn-outline"><i class="ti ti-file-text"></i> <?php echo __('letter.title'); ?></a>
        <a href="/contracts?action=edit&id=<?php echo $contract['id']; ?>" class="btn btn-outline"><i class="ti ti-edit"></i> <?php echo __('cd.edit'); ?></a>
        <a href="/contracts" class="btn btn-outline"><i class="ti ti-arrow-left"></i> <?php echo __('cd.back'); ?></a>
    </div>
</div>

<?php if (isset($_GET['uploaded'])): ?>
<div class="alert alert-success" style="margin-bottom:1rem;"><i class="ti ti-check"></i> <?php echo __('cf.doc.uploaded_ok'); ?></div>
<?php endif; ?>
<?php if ($uploadError): ?>
<div class="alert alert-error" style="margin-bottom:1rem;"><i class="ti ti-alert-circle"></i> <?php echo $errorMsg[$uploadError] ?? __('cf.doc.err.generic'); ?></div>
<?php endif; ?>

<?php
// Kündigungsfrist berechnen
$kbox = null;
if ($contract["cancellation_deadline"] ?? $contract["notice_date"]) {
    $today     = time();
    $notice_ts = strtotime($contract["cancellation_deadline"] ?? $contract["notice_date"]);
    $days      = (int)(($notice_ts - $today) / 86400);
    if ($days < 0) {
        $kbox = ['color'=>'#f87171','bg'=>'rgba(248,113,113,.08)','border'=>'rgba(248,113,113,.25)','icon'=>'ti-alert-circle',
            'text'=>__('cd.kbox.expired', ['days'=>abs($days)]),
            'sub'=>__('cd.kbox.expired_sub', ['date'=>date("d.m.Y",$notice_ts)])];
    } elseif ($days === 0) {
        $kbox = ['color'=>'#f87171','bg'=>'rgba(248,113,113,.08)','border'=>'rgba(248,113,113,.25)','icon'=>'ti-alert-triangle',
            'text'=>__('cd.kbox.today'),'sub'=>__('cd.kbox.today_sub', ['date'=>date("d.m.Y",$notice_ts)])];
    } elseif ($days <= 30) {
        $kbox = ['color'=>'#f87171','bg'=>'rgba(248,113,113,.08)','border'=>'rgba(248,113,113,.25)','icon'=>'ti-alarm',
            'text'=>__('cd.kbox.soon', ['days'=>$days]),'sub'=>__('cd.kbox.soon_sub', ['date'=>date("d.m.Y",$notice_ts)])];
    } elseif ($days <= 60) {
        $kbox = ['color'=>'#fbbf24','bg'=>'rgba(251,191,36,.08)','border'=>'rgba(251,191,36,.25)','icon'=>'ti-clock',
            'text'=>__('cd.kbox.soon', ['days'=>$days]),'sub'=>__('cd.kbox.soon_sub', ['date'=>date("d.m.Y",$notice_ts)])];
    } else {
        $kbox = ['color'=>'#34d399','bg'=>'rgba(52,211,153,.08)','border'=>'rgba(52,211,153,.25)','icon'=>'ti-circle-check',
            'text'=>__('cd.kbox.soon', ['days'=>$days]),'sub'=>__('cd.kbox.soon_sub', ['date'=>date("d.m.Y",$notice_ts)])];
    }
    // Nächste Verlängerung direkt aus DB (nicht bei unbefristeten Verträgen)
    if (!empty($contract["notice_date"]) && empty($contract["is_unlimited"])) {
        $kbox['renewal'] = date("d.m.Y", strtotime($contract["notice_date"]));
    }
}

// Bei Kündigung heute: nächstes Vertragsende berechnen (taggenau)
$kuendigung_heute = null;
if ($contract['cancellation_period_days'] && $contract['end_date']) {
    $today_dt  = new DateTime();
    $frist     = (int)$contract['cancellation_period_days'];
    $end_dt    = new DateTime($contract['end_date']);
    // Finde nächstes Enddatum nach heute + Kündigungsfrist
    $kuendigbar_ab = clone $today_dt;
    $kuendigbar_ab->modify("+{$frist} days");
    $next_end = clone $end_dt;
    while ($next_end <= $kuendigbar_ab) {
        $renewal = (int)($contract['renewal_interval_months'] ?? 1);
        $next_end->modify("+{$renewal} months");
    }
    $kuendigung_heute = $next_end->format('d.m.Y');
    if ($kbox) {
        $kbox['kuendigung_heute'] = $kuendigung_heute;
    }
}

// Erweiterte Felder
$typeLabels       = ['versicherung'=>__('cf.ctype.versicherung'),'mobilfunk'=>__('cf.ctype.mobilfunk'),'internet'=>__('cf.ctype.internet'),'darlehen'=>__('cf.ctype.darlehen'),'abo'=>__('cf.ctype.abo'),'wartung'=>__('cf.ctype.wartung'),'sonstig'=>__('cf.ctype.sonstig')];
$paymentLabels    = ['sepa'=>__('cf.pay.sepa'),'ueberweisung'=>__('cf.pay.ueberweisung'),'kreditkarte'=>__('cf.pay.kreditkarte'),'bar'=>__('cf.pay.bar'),'paypal'=>__('cf.pay.paypal')];
$counterpartyLabels = ['unternehmen'=>__('cf.cp.unternehmen'),'privatperson'=>__('cf.cp.privatperson'),'behoerde'=>__('cf.cp.behoerde')];
$extFields = [];
if ($contract['contract_type'])          $extFields[] = ['label'=>__('cd.ef.contract_type'),              'value'=>$typeLabels[$contract['contract_type']] ?? $contract['contract_type']];
if ($contract['counterparty_type'])      $extFields[] = ['label'=>__('cd.ef.counterparty'),       'value'=>$counterpartyLabels[$contract['counterparty_type']] ?? $contract['counterparty_type']];
if ($contract['payment_method'])         $extFields[] = ['label'=>__('cd.ef.payment'),               'value'=>$paymentLabels[$contract['payment_method']] ?? $contract['payment_method']];
if ($contract['iban'])                   $extFields[] = ['label'=>__('cd.ef.iban'),                      'value'=>$contract['iban'], 'mono'=>true];
if ($contract['mandate_reference'])      $extFields[] = ['label'=>__('cd.ef.mandate'),      'value'=>$contract['mandate_reference']];
if ($contract['minimum_term_months'])     $extFields[] = ['label'=>__('cd.ef.min_term'),          'value'=>$contract['minimum_term_months'].' Monate'];
if ($contract['renewal_interval_months']) $extFields[] = ['label'=>__('cd.ef.renewal'),   'value'=>$contract['renewal_interval_months'].' Monat(e)'];
if ($contract['auto_renewal'])           $extFields[] = ['label'=>__('cd.ef.auto_renewal'), 'value'=>__('cd.ef.yes')];
if ($contract['cancellation_period_days']) $extFields[] = ['label'=>__('cd.ef.cancel_period'),         'value'=>$contract['cancellation_period_days'].' Tage'];
if ($contract['cancellation_deadline'])  $extFields[] = ['label'=>__('cd.ef.cancel_deadline'),  'value'=>date('d.m.Y', strtotime($contract['cancellation_deadline']))];
if ($contract['loan_amount'])            $extFields[] = ['label'=>__('cd.ef.loan_amount'),           'value'=>number_format($contract['loan_amount'],2,',','.').' €'];
if ($contract['interest_rate'])          $extFields[] = ['label'=>__('cd.ef.interest'),                  'value'=>number_format($contract['interest_rate'],2,',','.').' %'];
if ($contract['monthly_rate'])           $extFields[] = ['label'=>__('cd.ef.monthly_rate'),           'value'=>number_format($contract['monthly_rate'],2,',','.').' €'];
if ($contract['deductible'])             $extFields[] = ['label'=>__('cd.ef.deductible'),         'value'=>number_format($contract['deductible'],2,',','.').' €'];
if ($contract['service_interval_months']) $extFields[] = ['label'=>__('cd.ef.service_interval'),        'value'=>$contract['service_interval_months'].' Monate'];

$hasDetails = !empty($extFields) || !empty($custom_fields)
    || (isset($contact) && $contact);
$personsCount = count($persons ?? []);
$docsCount    = count($documents ?? []);
$logCount     = count($comm_log ?? []);
?>

<!-- Tab-Navigation -->
<div style="max-width:1100px;margin:0 auto 1.25rem;">
    <div class="contract-tabs">
        <?php
        $tabs = [
            'uebersicht'   => ['icon'=>'ti-file-description', 'label'=>__('cd.tab.uebersicht')],
            'details'      => ['icon'=>'ti-list-details',     'label'=>__('cd.tab.details'),         'badge'=>$hasDetails ? null : null],
            'ansprechpartner' => ['icon'=>'ti-users',         'label'=>__('cd.tab.ansprechpartner'), 'badge'=>$personsCount ?: null],
            'dokumente'    => ['icon'=>'ti-files',            'label'=>__('cd.tab.dokumente'),       'badge'=>$docsCount ?: null],
        'kommunikation' => ['icon'=>'ti-message-dots',    'label'=>__('cd.tab.kommunikation'),   'badge'=>$logCount ?: null],
        ];
        foreach ($tabs as $key => $tab):
            $isActive = ($activeTab === $key);
            $baseUrl = '/contracts?action=view&id='.$contract['id'].'&tab='.$key;
        ?>
        <a href="<?php echo $baseUrl; ?>" class="contract-tab <?php echo $isActive ? 'active' : ''; ?>">
            <i class="ti <?php echo $tab['icon']; ?>"></i>
            <?php echo $tab['label']; ?>
            <?php if (!empty($tab['badge'])): ?>
            <span class="tab-badge"><?php echo $tab['badge']; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== TAB: ÜBERSICHT ===== -->
<?php if ($activeTab === 'uebersicht'): ?>

<?php if ($kbox): ?>
<div style="max-width:1100px;margin:0 auto 1rem;padding:1rem 1.25rem;background:<?php echo $kbox['bg']; ?>;border:1px solid <?php echo $kbox['border']; ?>;border-radius:12px;display:flex;align-items:center;gap:1rem;">
    <i class="ti <?php echo $kbox['icon']; ?>" style="font-size:1.6rem;color:<?php echo $kbox['color']; ?>;flex-shrink:0;"></i>
    <div>
        <div style="font-weight:600;color:<?php echo $kbox['color']; ?>;margin-bottom:.15rem;"><?php echo $kbox['text']; ?></div>
        <div style="font-size:.83rem;color:var(--text-muted);"><?php echo $kbox['sub']; ?>
            <?php if (!empty($kbox['renewal'])): ?>
            &nbsp;·&nbsp; <?php echo __('cd.kbox.renewal'); ?>: <strong style="color:rgba(255,255,255,.6);"><?php echo $kbox['renewal']; ?></strong>
            <?php endif; ?>
            <?php if (!empty($kbox['kuendigung_heute'])): ?>
            &nbsp;·&nbsp; <?php echo __('cd.kbox.if_today'); ?> <strong style="color:rgba(255,255,255,.6);"><?php echo $kbox['kuendigung_heute']; ?></strong>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto 1rem;">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem 1.5rem;">
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.contract_number'); ?></div>
            <div style="font-size:.9rem;font-family:monospace;color:#a5b4fc;"><?php echo htmlspecialchars($contract['contract_number'] ?? '–'); ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.client'); ?></div>
            <div style="font-size:.9rem;"><?php echo htmlspecialchars($contract['client_name']); ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.partner'); ?></div>
            <div style="font-size:.9rem;"><?php echo htmlspecialchars($contract['partner'] ?? '–'); ?></div>
        </div>
        <?php if (!empty($contract['partner_contract_number'])): ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.partner_contract_number'); ?></div>
            <div style="font-size:.9rem;"><?php echo htmlspecialchars($contract['partner_contract_number']); ?></div>
        </div>
        <?php endif; ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.status'); ?></div>
            <div style="font-size:.9rem;color:<?php echo $color; ?>;"><?php echo __('contracts.status.' . $contract['status']); ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.start_date'); ?></div>
            <div style="font-size:.9rem;"><?php echo $contract['start_date'] ? date('d.m.Y', strtotime($contract['start_date'])) : '–'; ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.end_date'); ?></div>
            <div style="font-size:.9rem;"><?php echo !empty($contract['is_unlimited']) ? __('cf.is_unlimited') : ($contract['end_date'] ? date('d.m.Y', strtotime($contract['end_date'])) : '–'); ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.cancellation_deadline'); ?></div>
            <div style="font-size:.9rem;"><?php echo $contract['cancellation_deadline'] ? date('d.m.Y', strtotime($contract['cancellation_deadline'])) : '–'; ?></div>
        </div>
        <?php if (empty($contract['is_unlimited'])): ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.renewal_date'); ?></div>
            <div style="font-size:.9rem;"><?php echo $contract['notice_date'] ? date('d.m.Y', strtotime($contract['notice_date'])) : '–'; ?></div>
        </div>
        <?php endif; ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.value'); ?></div>
            <div style="font-size:.9rem;"><?php echo $contract['value'] ? number_format($contract['value'],2,',','.').' €' : '–'; ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.interval'); ?></div>
            <div style="font-size:.9rem;"><?php echo $contract['billing_interval'] ? __('cf.interval.' . $contract['billing_interval']) : '–'; ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.category'); ?></div>
            <div style="font-size:.9rem;"><?php echo htmlspecialchars($contract['category_name'] ?? '–'); ?></div>
        </div>
        <?php if ($contract['plan']): ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.plan'); ?></div>
            <div style="font-size:.9rem;"><?php echo htmlspecialchars($contract['plan']); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($contract['description']): ?>
        <div style="grid-column:span 3;">
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.description'); ?></div>
            <div style="font-size:.9rem;"><?php echo nl2br(htmlspecialchars($contract['description'])); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($contract['notes']): ?>
        <div style="grid-column:span 3;">
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.notes'); ?></div>
            <div style="font-size:.9rem;"><?php echo nl2br(htmlspecialchars($contract['notes'])); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<!-- ===== TAB: DETAILS ===== -->
<?php if ($activeTab === 'details'): ?>

<?php if (!empty($extFields)): ?>
<div class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto 1rem;">
    <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;"><?php echo __('cd.extended_fields'); ?></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem 1.5rem;">
        <?php foreach ($extFields as $ef): ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo htmlspecialchars($ef['label']); ?></div>
            <div style="font-size:.9rem;<?php echo !empty($ef['mono']) ? 'font-family:monospace;' : ''; ?>"><?php echo htmlspecialchars($ef['value']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($custom_fields)): ?>
<div class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto 1rem;">
    <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;"><?php echo __('cd.custom_fields'); ?></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem 1.5rem;">
        <?php foreach ($custom_fields as $cf): ?>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo htmlspecialchars($cf['label']); ?></div>
            <div style="font-size:.9rem;">
                <?php if ($cf['field_type'] === 'url' && $cf['value']): ?>
                    <a href="<?php echo htmlspecialchars($cf['value']); ?>" target="_blank" style="color:#93c5fd;"><?php echo htmlspecialchars($cf['value']); ?></a>
                <?php elseif ($cf['field_type'] === 'email' && $cf['value']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($cf['value']); ?>" style="color:#93c5fd;"><?php echo htmlspecialchars($cf['value']); ?></a>
                <?php elseif ($cf['field_type'] === 'date' && $cf['value']): ?>
                    <?php echo date('d.m.Y', strtotime($cf['value'])); ?>
                <?php else: ?>
                    <?php echo htmlspecialchars($cf['value'] ?? '–'); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>


<?php if (empty($extFields) && empty($custom_fields)): ?>
<div class="card" style="padding:2.5rem 1.5rem;max-width:1100px;margin:0 auto;text-align:center;color:var(--text-muted);font-size:.85rem;">
    <i class="ti ti-list-details" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
    <?php echo __('cd.no_details'); ?>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- ===== TAB: ANSPRECHPARTNER ===== -->
<?php if ($activeTab === 'ansprechpartner'): ?>
<?php if (isset($contact) && $contact): ?>
<div class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto 1rem;">
    <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;"><?php echo __('cd.contact'); ?></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem 1.5rem;">
        <?php if ($contact['company']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.company'); ?></div><div style="font-size:.9rem;"><?php echo htmlspecialchars($contact['company']); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['first_name'] || $contact['last_name']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.name'); ?></div><div style="font-size:.9rem;"><?php echo htmlspecialchars(trim(($contact['first_name'] ?? '').' '.($contact['last_name'] ?? ''))); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['email']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.email'); ?></div><div style="font-size:.9rem;"><a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" style="color:#93c5fd;"><?php echo htmlspecialchars($contact['email']); ?></a></div></div>
        <?php endif; ?>
        <?php if ($contact['phone']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.phone'); ?></div><div style="font-size:.9rem;"><?php echo htmlspecialchars($contact['phone']); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['mobile']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.mobile'); ?></div><div style="font-size:.9rem;"><?php echo htmlspecialchars($contact['mobile']); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['street'] || $contact['city']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.address'); ?></div><div style="font-size:.9rem;"><?php echo htmlspecialchars($contact['street'] ?? ''); ?><br><?php echo htmlspecialchars(trim(($contact['zip'] ?? '').' '.($contact['city'] ?? ''))); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['iban']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.iban'); ?></div><div style="font-size:.9rem;font-family:monospace;"><?php echo htmlspecialchars($contact['iban']); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['bank']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.bank'); ?></div><div style="font-size:.9rem;"><?php echo htmlspecialchars($contact['bank']); ?></div></div>
        <?php endif; ?>
        <?php if ($contact['bic']): ?>
        <div><div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;"><?php echo __('cd.bic'); ?></div><div style="font-size:.9rem;font-family:monospace;"><?php echo htmlspecialchars($contact['bic']); ?></div></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($persons)): ?>
<div class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto;">
    <div style="display:flex;flex-direction:column;gap:.75rem;">
        <?php foreach ($persons as $p): ?>
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:.75rem;">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;">
                <i class="ti ti-user" style="color:var(--text-muted);"></i>
                <span style="font-weight:600;font-size:.9rem;"><?php echo htmlspecialchars(trim(($p["first_name"] ?? "")." ".($p["last_name"] ?? ""))); ?></span>
                <?php if ($p["role"]): ?>
                <span style="font-size:.75rem;color:var(--text-muted);background:rgba(255,255,255,.06);padding:.1rem .5rem;border-radius:20px;"><?php echo htmlspecialchars($p["role"]); ?></span>
                <?php endif; ?>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;font-size:.83rem;color:var(--text-muted);">
                <?php if ($p["email"]): ?><a href="mailto:<?php echo htmlspecialchars($p["email"]); ?>" style="color:#93c5fd;"><i class="ti ti-mail" style="font-size:.85rem;"></i> <?php echo htmlspecialchars($p["email"]); ?></a><?php endif; ?>
                <?php if ($p["phone"]): ?><span><i class="ti ti-phone" style="font-size:.85rem;"></i> <?php echo htmlspecialchars($p["phone"]); ?></span><?php endif; ?>
                <?php if ($p["mobile"]): ?><span><i class="ti ti-device-mobile" style="font-size:.85rem;"></i> <?php echo htmlspecialchars($p["mobile"]); ?></span><?php endif; ?>
                <?php if ($p["notes"]): ?><span><i class="ti ti-note" style="font-size:.85rem;"></i> <?php echo htmlspecialchars($p["notes"]); ?></span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<div class="card" style="padding:2.5rem 1.5rem;max-width:1100px;margin:0 auto;text-align:center;color:var(--text-muted);font-size:.85rem;">
    <i class="ti ti-users" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
    <?php echo __('cd.no_persons'); ?>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- ===== TAB: DOKUMENTE ===== -->
<?php if ($activeTab === 'dokumente'): ?>

<div class="card" style="max-width:1100px;margin:0 auto;">
    <div class="card-head">
        <span><i class="ti ti-files" style="margin-right:.4rem;"></i><?php echo __('cd.doc.title'); ?></span>
    </div>
    <div style="padding:1rem 1.25rem;">
        <form method="POST" action="/contracts?action=upload" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr auto auto;gap:.6rem;align-items:end;margin-bottom:1rem;">
            <input type="hidden" name="contract_id" value="<?php echo $contract['id']; ?>">
            <div class="form-group" style="margin:0;">
                <label style="font-size:.72rem;"><?php echo __('cd.doc.label'); ?></label>
                <input type="text" name="label" placeholder="<?php echo __('cd.doc.label'); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.72rem;"><?php echo __('cd.doc.file'); ?></label>
                <div style="position:relative;">
                <input type="file" name="document" id="doc-file-input" required style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                <div id="doc-file-label" style="padding:.35rem .75rem;font-size:.83rem;color:var(--text-muted);background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);border-radius:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                    <i class="ti ti-paperclip" style="margin-right:.3rem;"></i><?php echo __('cd.doc.no_file'); ?>
                </div>
            </div>
            <script>
            document.getElementById("doc-file-input").addEventListener("change", function(){
                var name = this.files[0] ? this.files[0].name : "<?php echo __('cd.doc.no_file'); ?>";
                document.getElementById("doc-file-label").innerHTML = '<i class="ti ti-paperclip" style="margin-right:.3rem;"></i>' + name;
            });
            </script>
            </div>
            <button type="submit" class="btn btn-primary" style="white-space:nowrap;"><i class="ti ti-upload"></i> <?php echo __('cd.doc.upload'); ?></button>
        </form>

        <?php if (empty($documents)): ?>
        <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;">
            <i class="ti ti-file-off" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
            <?php echo __('cd.doc.empty'); ?>
        </div>
        <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.08);">
                    <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cd.doc.col.file'); ?></th>
                    <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cd.doc.col.label'); ?></th>
                    <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cd.doc.col.size'); ?></th>
                    <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cd.doc.col.uploaded'); ?></th>
                    <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cd.doc.col.by'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                    <td style="padding:.6rem .4rem;">
                        <a href="/contracts?action=download&id=<?php echo $doc['id']; ?>" style="color:#93c5fd;text-decoration:none;display:flex;align-items:center;gap:.4rem;">
                            <i class="ti ti-file-download"></i>
                            <?php echo htmlspecialchars($doc['original_name']); ?>
                        </a>
                    </td>
                    <td style="padding:.6rem .4rem;color:var(--text-muted);"><?php echo htmlspecialchars($doc['label'] ?? '–'); ?></td>
                    <td style="padding:.6rem .4rem;color:var(--text-muted);"><?php echo round($doc['filesize']/1024, 1); ?> KB</td>
                    <td style="padding:.6rem .4rem;color:var(--text-muted);"><?php echo date('d.m.Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                    <td style="padding:.6rem .4rem;color:var(--text-muted);"><?php echo htmlspecialchars($doc['uploader']); ?></td>
                    <td style="padding:.6rem .4rem;text-align:right;">
                        <form method="POST" action="/contracts?action=delete_doc&tab=dokumente" onsubmit="return confirm(this.dataset.confirm)" data-confirm="<?php echo __('cd.doc.confirm'); ?>">
                            <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                            <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:.2rem .4rem;" title="<?php echo __('cd.doc.delete'); ?>">
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<!-- ===== TAB: KOMMUNIKATION ===== -->
<?php if ($activeTab === 'kommunikation'): ?>

<div style="max-width:1100px;margin:0 auto 1rem;">

    <!-- Neuer Eintrag -->
    <div class="card" style="padding:1.25rem 1.5rem;margin-bottom:1rem;">
        <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;"><?php echo __('cd.comm.new_entry'); ?></div>
        <form method="POST" action="/contracts?action=view&id=<?php echo $contract['id']; ?>&tab=kommunikation"
              style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:.6rem;">
            <div class="form-group" style="margin:0;grid-column:span 4;">
                <label style="font-size:.72rem;"><?php echo __('cd.comm.subject'); ?></label>
                <input type="text" name="subject" required placeholder="<?php echo __('cd.comm.ph.subject'); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.72rem;"><?php echo __('cd.comm.channel'); ?></label>
                <select name="channel">
                    <option value="telefon"><?php echo __('cd.comm.ch.telefon'); ?></option>
                    <option value="email"><?php echo __('cd.comm.ch.email'); ?></option>
                    <option value="brief"><?php echo __('cd.comm.ch.brief'); ?></option>
                    <option value="portal"><?php echo __('cd.comm.ch.portal'); ?></option>
                    <option value="persoenlich"><?php echo __('cd.comm.ch.persoenlich'); ?></option>
                    <option value="sonstig"><?php echo __('cd.comm.ch.sonstig'); ?></option>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.72rem;"><?php echo __('cd.comm.direction'); ?></label>
                <select name="direction">
                    <option value="ausgehend"><?php echo __('cd.comm.dir.ausgehend'); ?></option>
                    <option value="eingehend"><?php echo __('cd.comm.dir.eingehend'); ?></option>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label style="font-size:.72rem;"><?php echo __('cd.comm.datetime'); ?></label>
                <input type="datetime-local" name="logged_at" value="<?php echo date('Y-m-d\TH:i'); ?>">
            </div>
            <div class="form-group" style="margin:0;display:flex;align-items:flex-end;">
                <button type="submit" class="btn btn-primary" style="width:100%;"><i class="ti ti-plus"></i> <?php echo __('cd.comm.submit'); ?></button>
            </div>
            <div class="form-group" style="margin:0;grid-column:span 4;">
                <label style="font-size:.72rem;"><?php echo __('cd.comm.note'); ?></label>
                <textarea name="body" rows="2" placeholder="<?php echo __('cd.comm.ph.note'); ?>" style="resize:vertical;"></textarea>
            </div>
        </form>
    </div>

    <!-- Log-Liste -->
    <?php if (empty($comm_log)): ?>
    <div class="card" style="padding:2.5rem 1.5rem;text-align:center;color:var(--text-muted);font-size:.85rem;">
        <i class="ti ti-message-dots" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
        <?php echo __('cd.comm.empty'); ?>
    </div>
    <?php else: ?>
    <div class="card" style="padding:1.25rem 1.5rem;">
        <div style="display:flex;flex-direction:column;gap:.6rem;">
        <?php
        $channelIcons = ['telefon'=>'ti-phone','email'=>'ti-mail','brief'=>'ti-mail-opened',
                         'portal'=>'ti-world','persoenlich'=>'ti-user','sonstig'=>'ti-message'];
        $channelLabels = ['telefon'=>__('cd.comm.ch.telefon'),'email'=>__('cd.comm.ch.email'),'brief'=>__('cd.comm.ch.brief'),'portal'=>__('cd.comm.ch.portal'),'persoenlich'=>__('cd.comm.ch.persoenlich'),'sonstig'=>__('cd.comm.ch.sonstig')];
        $directionColors = ['eingehend'=>'#4ade80','ausgehend'=>'#60a5fa'];
        foreach ($comm_log as $entry):
        ?>
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:.75rem 1rem;">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;flex-wrap:wrap;">
                <i class="ti <?php echo $channelIcons[$entry['channel']] ?? 'ti-message'; ?>"
                   style="color:var(--text-muted);font-size:.95rem;"></i>
                <span style="font-weight:600;font-size:.9rem;"><?php echo htmlspecialchars($entry['subject']); ?></span>
                <span style="font-size:.75rem;padding:.1rem .5rem;border-radius:20px;background:rgba(255,255,255,.06);color:var(--text-muted);">
                    <?php echo $channelLabels[$entry['channel']] ?? $entry['channel']; ?>
                </span>
                <span style="font-size:.75rem;padding:.1rem .5rem;border-radius:20px;background:rgba(255,255,255,.06);color:<?php echo $directionColors[$entry['direction']]; ?>;">
                    <?php echo $entry['direction'] === 'ausgehend' ? __('cd.comm.dir.ausgehend') : __('cd.comm.dir.eingehend'); ?>
                </span>
                <span style="font-size:.78rem;color:var(--text-muted);margin-left:auto;">
                    <?php echo date('d.m.Y H:i', strtotime($entry['logged_at'])); ?>
                    &nbsp;·&nbsp; <?php echo htmlspecialchars($entry['user_name']); ?>
                </span>
                <form method="POST" action="/contracts?action=view&id=<?php echo $contract['id']; ?>&tab=kommunikation_delete"
                      onsubmit="return confirm(this.dataset.confirm)" data-confirm="<?php echo __('cd.comm.confirm_delete'); ?>"" style="margin:0;">
                    <input type="hidden" name="log_id" value="<?php echo $entry['id']; ?>">
                    <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:.1rem .3rem;" title="<?php echo __('cd.doc.delete'); ?>">
                        <i class="ti ti-trash" style="font-size:.85rem;"></i>
                    </button>
                </form>
            </div>
            <?php if ($entry['body']): ?>
            <div style="font-size:.83rem;color:var(--text-muted);padding-left:1.5rem;white-space:pre-wrap;"><?php echo htmlspecialchars($entry['body']); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php endif; ?>