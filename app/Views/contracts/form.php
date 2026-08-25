<?php $activeTab = $_GET['tab'] ?? 'stammdaten'; ?>

<div class="page-header">
    <h2><?php echo isset($contract) ? __('cf.edit') : __('cf.new'); ?></h2>
    <div style="display:flex;gap:.5rem;">
        <?php if (isset($contract)): ?>
            <a href="/contracts/<?php echo $contract['id']; ?>/letter" class="btn btn-outline"><i class="ti ti-file-text"></i> <?php echo __('letter.title'); ?></a>
        <?php endif; ?>
        <a href="/contracts" class="btn btn-outline"><i class="ti ti-arrow-left"></i><?php echo __('cf.back'); ?></a>
    </div>
</div>

<form method="POST" action="/contracts">
    <input type="hidden" name="action" value="<?php echo isset($contract) ? 'update' : 'store'; ?>">
    <?php if (isset($contract)): ?>
        <input type="hidden" name="id" value="<?php echo $contract['id']; ?>">
    <?php endif; ?>
    <input type="hidden" name="form_tab" id="form_tab" value="<?php echo htmlspecialchars($activeTab); ?>">

    <!-- Tab-Navigation -->
    <div style="max-width:1100px;margin:0 auto 1.25rem;">
        <div style="display:flex;gap:.25rem;border-bottom:1px solid rgba(255,255,255,.1);padding-bottom:0;">
            <?php
            $tabs = [
                'stammdaten'      => ['icon'=>'ti-file-description', 'label'=>__('cf.tab.stammdaten')],
                'details'         => ['icon'=>'ti-list-details',     'label'=>__('cf.tab.details')],
                'ansprechpartner' => ['icon'=>'ti-users',            'label'=>__('cf.tab.ansprechpartner')],
                'eigene-felder'   => ['icon'=>'ti-tag',              'label'=>__('cf.tab.eigene_felder')],
                'dokumente'       => ['icon'=>'ti-files',            'label'=>__('cf.tab.dokumente'),       'show_if_new'=>false],
            ];
            foreach ($tabs as $key => $tab):
                $isActive = ($activeTab === $key);
                if (isset($tab['show_if_new']) && $tab['show_if_new'] === false && !isset($contract)) continue;
            ?>
            <a href="#" onclick="switchTab('<?php echo $key; ?>');return false;" id="tabnav-<?php echo $key; ?>" style="
                display:inline-flex;align-items:center;gap:.4rem;
                padding:.55rem 1rem;
                font-size:.83rem;font-weight:500;
                border-radius:8px 8px 0 0;
                text-decoration:none;
                border:1px solid <?php echo $isActive ? 'rgba(255,255,255,.1)' : 'transparent'; ?>;
                border-bottom:<?php echo $isActive ? '1px solid var(--bg-card,#1e2535)' : '1px solid transparent'; ?>;
                margin-bottom:-1px;
                color:<?php echo $isActive ? '#e2e8f0' : 'var(--text-muted)'; ?>;
                background:<?php echo $isActive ? 'rgba(255,255,255,.05)' : 'transparent'; ?>;
                transition:color .15s,background .15s;
            ">
                <i class="ti <?php echo $tab['icon']; ?>"></i>
                <?php echo $tab['label']; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ===== TAB: STAMMDATEN ===== -->
    <div id="tab-stammdaten" class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto;">
        <div class="form-group">
            <label><?php echo __('cf.title'); ?></label>
            <input type="text" name="title" required value="<?php echo htmlspecialchars($contract['title'] ?? ''); ?>" placeholder="<?php echo __('cf.ph.title'); ?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;position:relative;">
                <label><?php echo __('cf.partner'); ?></label>
                <input type="text" name="partner" id="partner_input" autocomplete="off"
                    value="<?php echo htmlspecialchars($contract['partner'] ?? ''); ?>"
                    placeholder="<?php echo __('cf.ph.partner'); ?>">
                <div id="partner_hint" style="display:none;font-size:.78rem;color:#34d399;margin-top:.3rem;"><i class="ti ti-circle-check"></i> Kontaktdaten übernommen – im Tab „Ansprechpartner" prüfen</div>
                <div id="partner_suggestions" style="display:none;position:absolute;z-index:999;background:#1e2a3a;border:1px solid rgba(99,102,241,.4);border-radius:8px;width:100%;max-height:220px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.5);margin-top:2px;"></div>
            </div>
            <script>
            (function() {
                var input = document.getElementById('partner_input');
                var box   = document.getElementById('partner_suggestions');
                var timer = null;
                input.addEventListener('input', function() {
                    clearTimeout(timer);
                    var q = input.value.trim();
                    if (q.length < 2) { box.style.display = 'none'; return; }
                    timer = setTimeout(function() {
                        fetch('/contracts/partner-suggest?q=' + encodeURIComponent(q))
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (!data.length) { box.style.display = 'none'; return; }
                                box.innerHTML = '';
                                data.forEach(function(p) {
                                    var item = document.createElement('div');
                                    item.style.cssText = 'padding:.5rem .85rem;cursor:pointer;font-size:.9rem;border-bottom:1px solid rgba(255,255,255,.06);';
                                    item.innerHTML = '<strong>' + p.partner + '</strong>' +
                                        (p.customer_number ? ' <span style="color:var(--text-muted);font-size:.8rem;">Kundennr. ' + p.customer_number + '</span>' : '') +
                                        (p.city ? '<br><span style="color:var(--text-muted);font-size:.8rem;">' + (p.company || '') + (p.company && p.city ? ' · ' : '') + p.city + '</span>' : '');
                                    item.addEventListener('mousedown', function(e) {
                                        e.preventDefault();
                                        input.value = p.partner;
                                        // Felder befüllen
                                        var fields = {
                                            'customer_number': p.customer_number || '',
                                            'cc_company':      p.company || '',
                                            'cc_street':       p.street || '',
                                            'cc_zip':          p.zip || '',
                                            'cc_city':         p.city || '',
                                            'cc_email':        p.email || '',
                                            'cc_phone':        p.phone || '',
                                            'cc_mobile':       p.mobile || '',
                                        };
                                        Object.keys(fields).forEach(function(name) {
                                            var el = document.querySelector('[name="' + name + '"]');
                                            if (el && fields[name]) el.value = fields[name];
                                        });
                                        box.style.display = 'none';
                                        // Hinweis anzeigen
                                        var hint = document.getElementById('partner_hint');
                                        if (hint) { hint.style.display = 'block'; }
                                    });
                                    item.addEventListener('mouseover', function() { item.style.background = 'rgba(255,255,255,.06)'; });
                                    item.addEventListener('mouseout',  function() { item.style.background = ''; });
                                    box.appendChild(item);
                                });
                                box.style.display = 'block';
                            });
                    }, 250);
                });
                document.addEventListener('click', function(e) {
                    if (!box.contains(e.target) && e.target !== input) box.style.display = 'none';
                });
            })();
            </script>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.customer_number'); ?></label>
                <input type="text" name="customer_number" value="<?php echo htmlspecialchars($contract['customer_number'] ?? ''); ?>" placeholder="z.B. 6073477486">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.partner_contract_number'); ?></label>
                <input type="text" name="partner_contract_number" value="<?php echo htmlspecialchars($contract['partner_contract_number'] ?? ''); ?>" placeholder="z.B. 123456789">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.client'); ?></label>
                <select name="client_id" required id="client_id_sel">
                    <option value=""><?php echo __('cf.select_client'); ?></option>
                    <?php foreach ($clients as $cl): ?>
                        <option value="<?php echo $cl['id']; ?>" <?php echo ($contract['client_id'] ?? '') == $cl['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cl['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="height:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.category'); ?></label>
                <select name="category_id">
                    <option value=""><?php echo __('cf.select_none'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($contract['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.status'); ?></label>
                <select name="status">
                    <?php foreach (['aktiv' => __('contracts.status.aktiv'),'gekuendigt' => __('contracts.status.gekuendigt'),'abgelaufen' => __('contracts.status.abgelaufen'),'pausiert' => __('contracts.status.pausiert')] as $st => $sl): ?>
                        <option value="<?php echo $st; ?>" <?php echo ($contract['status'] ?? 'aktiv') === $st ? 'selected' : ''; ?>>
                            <?php echo $sl; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="height:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.min_term'); ?></label>
                <input type="number" name="minimum_term_months" id="minimum_term_months" min="1" value="<?php echo htmlspecialchars($contract['minimum_term_months'] ?? ''); ?>" placeholder="12">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.renewal'); ?></label>
                <input type="number" name="renewal_interval_months" id="renewal_interval_months" min="1" value="<?php echo htmlspecialchars($contract['renewal_interval_months'] ?? ''); ?>" placeholder="1">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.cancel_period'); ?></label>
                <input type="number" name="cancellation_period_days" id="cancellation_period_days" min="1" value="<?php echo htmlspecialchars($contract['cancellation_period_days'] ?? ''); ?>" placeholder="30">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.start_date'); ?></label>
                <input type="date" name="start_date" id="start_date" value="<?php echo $contract['start_date'] ?? ''; ?>">
            </div>
        </div>
        <div style="height:.75rem;"></div>
        <?php if (isset($contract) && !empty($contract['id'])): ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.end_date'); ?> <span style="font-size:.7rem;color:var(--text-muted);"><?php echo __('cf.end_date_calc'); ?></span></label>
                <input type="date" name="end_date" id="end_date" value="<?php echo $contract['end_date'] ?? ''; ?>" readonly style="opacity:.7;cursor:not-allowed;">
            </div>
            <input type="hidden" name="cancellation_deadline" id="cancellation_deadline" value="<?php echo $contract['cancellation_deadline'] ?? ''; ?>">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.notice_date'); ?> <span style="font-size:.7rem;color:var(--text-muted);"><?php echo __('cf.end_date_calc'); ?></span></label>
                <input type="date" name="notice_date" id="notice_date" value="<?php echo $contract['notice_date'] ?? ''; ?>" readonly style="opacity:.7;cursor:not-allowed;">
            </div>
        </div>
        <?php else: ?>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.end_date'); ?> <span style="font-size:.7rem;color:var(--text-muted);"><?php echo __('cf.end_date_calc'); ?></span></label>
                <input type="date" name="end_date" id="end_date" value="" readonly style="opacity:.7;cursor:not-allowed;">
            </div>
            <input type="hidden" name="cancellation_deadline" id="cancellation_deadline" value="">
            <input type="hidden" name="notice_date" id="notice_date" value="">
        </div>
        <?php endif; ?>
        <div style="height:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.value'); ?></label>
                <input type="number" step="0.01" name="value" value="<?php echo $contract['value'] ?? ''; ?>" placeholder="0,00">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.direction'); ?></label>
                <select name="direction">
                    <?php foreach (['ausgabe' => __('cf.direction.ausgabe'), 'einnahme' => __('cf.direction.einnahme')] as $dv => $dl): ?>
                        <option value="<?php echo $dv; ?>" <?php echo ($contract['direction'] ?? 'ausgabe') === $dv ? 'selected' : ''; ?>>
                            <?php echo $dl; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.interval'); ?></label>
                <select name="billing_interval">
                    <?php foreach (['einmalig' => __('cf.interval.einmalig'),'monatlich' => __('cf.interval.monatlich'),'quartalsweise' => __('cf.interval.quartalsweise'),'jaehrlich' => __('cf.interval.jaehrlich')] as $iv => $il): ?>
                        <option value="<?php echo $iv; ?>" <?php echo ($contract['billing_interval'] ?? 'jaehrlich') === $iv ? 'selected' : ''; ?>>
                            <?php echo $il; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.plan'); ?></label>
                <input type="text" name="plan" value="<?php echo htmlspecialchars($contract['plan'] ?? ''); ?>" placeholder="<?php echo __('cf.ph.plan'); ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.phone_number'); ?></label>
                <input type="text" name="phone_number" value="<?php echo htmlspecialchars($contract['phone_number'] ?? ''); ?>" placeholder="z.B. +49 151 12345678">
            </div>
        </div>
        <div style="height:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.description'); ?></label>
                <textarea name="description" rows="3" placeholder="<?php echo __('cf.ph.description'); ?>"><?php echo htmlspecialchars($contract['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.notes'); ?></label>
                <textarea name="notes" rows="3" placeholder="<?php echo __('cf.ph.notes'); ?>"><?php echo htmlspecialchars($contract['notes'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>

    <!-- ===== TAB: DETAILS ===== -->
    <div id="tab-details" class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto;display:none;">
        <!-- Erweiterte Felder -->
        <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">Erweiterte Felder</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.contract_type'); ?></label>
                <select name="contract_type" id="contract_type_sel">
                    <option value=""><?php echo __('cf.type.none'); ?></option>
                    <?php foreach (['versicherung'=>__('cf.ctype.versicherung'),'mobilfunk'=>__('cf.ctype.mobilfunk'),'internet'=>__('cf.ctype.internet'),'darlehen'=>__('cf.ctype.darlehen'),'abo'=>__('cf.ctype.abo'),'wartung'=>__('cf.ctype.wartung'),'sonstig'=>__('cf.ctype.sonstig')] as $val=>$label): ?>
                        <option value="<?php echo $val; ?>" <?php echo ($contract['contract_type'] ?? '') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.counterparty_type'); ?></label>
                <select name="counterparty_type">
                    <option value=""><?php echo __('cf.type.none'); ?></option>
                    <?php foreach (['unternehmen'=>__('cf.cp.unternehmen'),'privatperson'=>__('cf.cp.privatperson'),'behoerde'=>__('cf.cp.behoerde')] as $val=>$label): ?>
                        <option value="<?php echo $val; ?>" <?php echo ($contract['counterparty_type'] ?? '') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="height:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.payment_method'); ?></label>
                <select name="payment_method">
                    <option value=""><?php echo __('cf.select_none'); ?></option>
                    <?php foreach (['sepa'=>__('cf.pay.sepa'),'ueberweisung'=>__('cf.pay.ueberweisung'),'kreditkarte'=>__('cf.pay.kreditkarte'),'bar'=>__('cf.pay.bar'),'paypal'=>__('cf.pay.paypal')] as $val=>$label): ?>
                        <option value="<?php echo $val; ?>" <?php echo ($contract['payment_method'] ?? '') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.iban'); ?></label>
                <input type="text" name="iban" value="<?php echo htmlspecialchars($contract['iban'] ?? ''); ?>" placeholder="DE00 0000 0000 0000 0000 00">
            </div>
            <div class="form-group" style="margin:0;">
                <label><?php echo __('cf.mandate_ref'); ?></label>
                <input type="text" name="mandate_reference" value="<?php echo htmlspecialchars($contract['mandate_reference'] ?? ''); ?>">
            </div>
        </div>
        <div style="height:.75rem;"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
            <div class="form-group" style="margin:0;">
                <label style="display:flex;align-items:center;gap:.5rem;">
                    <input type="checkbox" name="auto_renewal" value="1" <?php echo !empty($contract['auto_renewal']) ? 'checked' : ''; ?>><?php echo __('cf.auto_renewal'); ?>
                </label>
            </div>

        </div>
        <div id="fields-darlehen" style="display:none;">
            <div style="height:.75rem;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.loan_amount'); ?></label><input type="number" step="0.01" name="loan_amount" value="<?php echo htmlspecialchars($contract['loan_amount'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.interest_rate'); ?></label><input type="number" step="0.01" name="interest_rate" value="<?php echo htmlspecialchars($contract['interest_rate'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.monthly_rate'); ?></label><input type="number" step="0.01" name="monthly_rate" value="<?php echo htmlspecialchars($contract['monthly_rate'] ?? ''); ?>"></div>
            </div>
        </div>
        <div id="fields-versicherung" style="display:none;">
            <div style="height:.75rem;"></div>
            <div style="display:grid;grid-template-columns:1fr 3fr;gap:.75rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.deductible'); ?></label><input type="number" step="0.01" name="deductible" value="<?php echo htmlspecialchars($contract['deductible'] ?? ''); ?>"></div>
            </div>
        </div>
        <div id="fields-wartung" style="display:none;">
            <div style="height:.75rem;"></div>
            <div style="display:grid;grid-template-columns:1fr 3fr;gap:.75rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.service_interval'); ?></label><input type="number" name="service_interval_months" value="<?php echo htmlspecialchars($contract['service_interval_months'] ?? ''); ?>" placeholder="12"></div>
            </div>
        </div>

    </div>

    <!-- ===== TAB: ANSPRECHPARTNER ===== -->
    <div id="tab-ansprechpartner" class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto;display:none;">
        <!-- Kontaktdaten (nur nicht-private Mandanten) -->
        <div id="contact-block">
            <div style="height:1.25rem;"></div>
            <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">Kontaktdaten</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.company'); ?></label><input type="text" name="cc_company" value="<?php echo htmlspecialchars($contact['company'] ?? ''); ?>" placeholder="Firmenname"></div>
                <div class="form-group" style="margin:0;"><label>E-Mail</label><input type="email" name="cc_email" value="<?php echo htmlspecialchars($contact['email'] ?? ''); ?>" placeholder="mail@beispiel.de"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.first_name'); ?></label><input type="text" name="cc_first_name" value="<?php echo htmlspecialchars($contact['first_name'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.last_name'); ?></label><input type="text" name="cc_last_name" value="<?php echo htmlspecialchars($contact['last_name'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.phone'); ?></label><input type="text" name="cc_phone" value="<?php echo htmlspecialchars($contact['phone'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.mobile'); ?></label><input type="text" name="cc_mobile" value="<?php echo htmlspecialchars($contact['mobile'] ?? ''); ?>"></div>
            </div>
            <div style="height:.75rem;"></div>
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:.75rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.street'); ?></label><input type="text" name="cc_street" value="<?php echo htmlspecialchars($contact['street'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.zip'); ?></label><input type="text" name="cc_zip" value="<?php echo htmlspecialchars($contact['zip'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.city'); ?></label><input type="text" name="cc_city" value="<?php echo htmlspecialchars($contact['city'] ?? ''); ?>"></div>
            </div>
            <div style="height:.75rem;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;">
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.iban'); ?></label><input type="text" name="cc_iban" value="<?php echo htmlspecialchars($contact['iban'] ?? ''); ?>" placeholder="DE00 0000 0000 0000 0000 00"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.bank'); ?></label><input type="text" name="cc_bank" value="<?php echo htmlspecialchars($contact['bank'] ?? ''); ?>"></div>
                <div class="form-group" style="margin:0;"><label><?php echo __('cf.bic'); ?></label><input type="text" name="cc_bic" value="<?php echo htmlspecialchars($contact['bic'] ?? ''); ?>" placeholder="DEUTDEDB"></div>
            </div>
        </div>
        <div style="height:1.25rem;"></div>
        <div id="persons-list">
            <?php foreach (($persons ?? []) as $i => $p): ?>
            <div class="person-row" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:.75rem;margin-bottom:.75rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-bottom:.5rem;">
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.person.role'); ?></label><input type="text" name="person_role[]" value="<?php echo htmlspecialchars($p['role'] ?? ''); ?>" placeholder="<?php echo __('cf.ph.person_role'); ?>"></div>
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.first_name'); ?></label><input type="text" name="person_first_name[]" value="<?php echo htmlspecialchars($p['first_name'] ?? ''); ?>"></div>
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.last_name'); ?></label><input type="text" name="person_last_name[]" value="<?php echo htmlspecialchars($p['last_name'] ?? ''); ?>"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-bottom:.5rem;">
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.email'); ?></label><input type="email" name="person_email[]" value="<?php echo htmlspecialchars($p['email'] ?? ''); ?>"></div>
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.phone'); ?></label><input type="text" name="person_phone[]" value="<?php echo htmlspecialchars($p['phone'] ?? ''); ?>"></div>
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.mobile'); ?></label><input type="text" name="person_mobile[]" value="<?php echo htmlspecialchars($p['mobile'] ?? ''); ?>"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr auto;gap:.5rem;align-items:end;">
                    <div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.person.notes'); ?></label><input type="text" name="person_notes[]" value="<?php echo htmlspecialchars($p['notes'] ?? ''); ?>"></div>
                    <button type="button" onclick="this.closest('.person-row').remove()" class="btn btn-outline" style="color:#f87171;border-color:#f87171;padding:.35rem .6rem;"><i class="ti ti-trash"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addPerson()" class="btn btn-outline" style="font-size:.82rem;padding:.3rem .75rem;">
            <i class="ti ti-user-plus"></i><?php echo __('cf.person.add'); ?>
        </button>
    </div>

    <!-- ===== TAB: EIGENE FELDER ===== -->
    <div id="tab-eigene-felder" class="card" style="padding:1.25rem 1.5rem;max-width:1100px;margin:0 auto;display:none;">
        <div id="custom-fields-list">
            <?php foreach (($custom_fields ?? []) as $cf): ?>
            <div class="custom-field-row" style="display:grid;grid-template-columns:2fr 3fr 1fr auto;gap:.5rem;margin-bottom:.5rem;align-items:center;">
                <input type="text" name="custom_labels[]" placeholder="<?php echo __('cf.ph.custom_label'); ?>" value="<?php echo htmlspecialchars($cf['label']); ?>">
                <input type="<?php echo in_array($cf['field_type'], ['date','url','email','number']) ? $cf['field_type'] : 'text'; ?>" name="custom_values[]" value="<?php echo htmlspecialchars($cf['value'] ?? ''); ?>">
                <select name="custom_types[]" onchange="syncInputType(this)">
                    <?php foreach (['text'=>__('cf.custom.text'),'number'=>__('cf.custom.number'),'date'=>__('cf.custom.date'),'url'=>'URL','email'=>'E-Mail'] as $tv=>$tl): ?>
                        <option value="<?php echo $tv; ?>" <?php echo $cf['field_type']===$tv?'selected':''; ?>><?php echo $tl; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" onclick="this.closest('.custom-field-row').remove()" style="background:none;border:none;color:var(--danger,#e74c3c);cursor:pointer;font-size:1.1rem;">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addCustomField()" class="btn btn-outline" style="margin-top:.25rem;font-size:.82rem;padding:.3rem .75rem;">
            <i class="ti ti-plus"></i><?php echo __('cf.custom.add'); ?>
        </button>
    </div>

    <!-- Speichern (immer sichtbar) -->
    <div style="max-width:1100px;margin:.75rem auto 0;display:flex;gap:1rem;justify-content:flex-end;">
        <a href="/contracts" class="btn btn-outline"><?php echo __('cf.cancel'); ?></a>
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i><?php echo __('cf.save'); ?></button>
    </div>
</form>
<script>
window.cf_i18n = {
    custom_label_ph: <?php echo json_encode(__('cf.ph.custom_label')); ?>,
    custom_value_ph: <?php echo json_encode(__('cf.ph.custom_value')); ?>,
    person_role_ph:  <?php echo json_encode(__('cf.ph.person_role')); ?>,
    type_text:       <?php echo json_encode(__('cf.custom.text')); ?>,
    type_number:     <?php echo json_encode(__('cf.custom.number')); ?>,
    type_date:       <?php echo json_encode(__('cf.custom.date')); ?>
};
</script>
<script>
(function() {
    function lastDayOfMonth(y, m) {
        return new Date(y, m + 1, 0);
    }
    function toISO(d) {
        return d.getFullYear() + "-" +
            String(d.getMonth()+1).padStart(2,"0") + "-" +
            String(d.getDate()).padStart(2,"0");
    }
    function calcDates() {
        var startEl = document.getElementById("start_date");
        var termEl  = document.getElementById("minimum_term_months");
        var daysEl  = document.getElementById("cancellation_period_days");
        if (!startEl || !termEl) return;

        var start = startEl.value;
        var term  = parseInt(termEl.value);
        var days  = daysEl ? parseInt(daysEl.value) : 0;

        var endEl  = document.getElementById("end_date");
        var dlEl   = document.getElementById("cancellation_deadline");
        var notEl  = document.getElementById("notice_date");

        if (!start || !term) {
            if (endEl) endEl.value = "";
            if (dlEl)  dlEl.value  = "";
            if (notEl) notEl.value = "";
            return;
        }

        var s = new Date(start);
        var endMonth = s.getMonth() + (term - 1);
        var endYear  = s.getFullYear() + Math.floor(endMonth / 12);
        endMonth = endMonth % 12;
        var end = lastDayOfMonth(endYear, endMonth);

        if (endEl) endEl.value = toISO(end);

        // Automatische Verlängerung bis = Enddatum + Verlängerungsintervall
        var renewal = parseInt(document.getElementById("renewal_interval_months") ? document.getElementById("renewal_interval_months").value : 0);
        if (notEl && renewal > 0) {
            var renewMonth = end.getMonth() + renewal;
            var renewYear  = end.getFullYear() + Math.floor(renewMonth / 12);
            renewMonth = renewMonth % 12;
            var renewEnd = lastDayOfMonth(renewYear, renewMonth);
            notEl.value = toISO(renewEnd);
        } else if (notEl) {
            notEl.value = toISO(end);
        }

        if (days && days > 0) {
            var dl = new Date(end);
            dl.setDate(dl.getDate() - days);
            dl = lastDayOfMonth(dl.getFullYear(), dl.getMonth());
            if (dl >= end) {
                dl.setMonth(dl.getMonth() - 1);
                dl = lastDayOfMonth(dl.getFullYear(), dl.getMonth());
            }
            if (dlEl) dlEl.value = toISO(dl);
        } else {
            if (dlEl) dlEl.value = "";
        }
    }

    ["start_date","minimum_term_months","cancellation_period_days"].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener("change", calcDates);
    });
})();
</script>

<!-- ===== TAB: DOKUMENTE (nur Edit, außerhalb Form) ===== -->
<?php if (isset($contract)): ?>
    <div id="tab-dokumente" class="card" style="max-width:1100px;margin:0 auto;display:none;">
        <div class="card-head">
            <span><i class="ti ti-files" style="margin-right:.4rem;"></i>Dokumente</span>
        </div>
        <div style="padding:1rem 1.25rem;">
            <?php if (isset($_GET['uploaded'])): ?>
            <div class="alert alert-success" style="margin-bottom:1rem;"><i class="ti ti-check"></i> <?php echo __('cf.doc.uploaded_ok'); ?></div>
            <?php endif; ?>
            <?php $errorMsg = ['1'=>__('cf.doc.err.1'),'2'=>__('cf.doc.err.2'),'3'=>__('cf.doc.err.3')]; ?>
            <?php if (!empty($uploadError)): ?>
            <div class="alert alert-error" style="margin-bottom:1rem;"><i class="ti ti-alert-circle"></i> <?php echo $errorMsg[$uploadError] ?? __('cf.doc.err.generic'); ?></div>
            <?php endif; ?>
            <form method="POST" action="/contracts?action=upload" enctype="multipart/form-data" style="display:flex;gap:.6rem;align-items:flex-end;margin-bottom:1rem;flex-wrap:nowrap;">
                <input type="hidden" name="contract_id" value="<?php echo $contract['id']; ?>">
                <input type="hidden" name="from_edit" value="1">
                <div class="form-group" style="margin:0;flex:1;">
                    <label style="font-size:.72rem;"><?php echo __('cf.doc.label'); ?></label>
                    <input type="text" name="label" placeholder="<?php echo __('cf.ph.doc_label'); ?>">
                </div>
                <div class="form-group" style="margin:0;flex-shrink:0;">
                    <label style="font-size:.72rem;"><?php echo __('cf.doc.file'); ?></label>
                    <div style="position:relative;">
                        <input type="file" name="document" id="doc-file-input-edit" required style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                        <div id="doc-file-label-edit" style="padding:.35rem .75rem;font-size:.83rem;color:var(--text-muted);background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.12);border-radius:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:180px;">
                            <i class="ti ti-paperclip" style="margin-right:.3rem;"></i><?php echo __('cf.doc.no_file'); ?>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;flex-shrink:0;"><i class="ti ti-upload"></i><?php echo __('cf.doc.upload'); ?></button>
            </form>
            <?php if (empty($documents)): ?>
            <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;">
                <i class="ti ti-file-off" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3;"></i>
                <?php echo __('cf.doc.empty'); ?>
            </div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.08);">
                        <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cf.doc.col.file'); ?></th>
                        <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cf.doc.col.label'); ?></th>
                        <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cf.doc.col.size'); ?></th>
                        <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cf.doc.col.uploaded'); ?></th>
                        <th style="text-align:left;padding:.5rem .4rem;font-size:.72rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.05em;"><?php echo __('cf.doc.col.by'); ?></th>
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
                            <form method="POST" action="/contracts?action=delete_doc" onsubmit="return confirm(this.dataset.confirm)" data-confirm="<?php echo __('cf.doc.confirm_delete'); ?>">
                                <input type="hidden" name="doc_id" value="<?php echo $doc['id']; ?>">
                                <input type="hidden" name="from_edit" value="1">
                                <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:.2rem .4rem;" title="<?php echo __('cf.doc.delete'); ?>">
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



<script>
var allTabs = ['stammdaten','details','ansprechpartner','eigene-felder','dokumente'];
var activeTab = '<?php echo htmlspecialchars($activeTab); ?>';

function switchTab(key) {
    allTabs.forEach(function(t) {
        var panel = document.getElementById('tab-' + t);
        var nav   = document.getElementById('tabnav-' + t);
        if (!panel || !nav) return;
        var isActive = (t === key);
        panel.style.display = isActive ? '' : 'none';
        nav.style.color      = isActive ? '#e2e8f0' : 'var(--text-muted)';
        nav.style.background = isActive ? 'rgba(255,255,255,.05)' : 'transparent';
        nav.style.border     = isActive ? '1px solid rgba(255,255,255,.1)' : '1px solid transparent';
        nav.style.borderBottom = isActive ? '1px solid var(--bg-card,#1e2535)' : '1px solid transparent';
    });
    document.getElementById('form_tab').value = key;
    activeTab = key;
}

// Typ-spezifische Felder
var typeMap = {versicherung:'fields-versicherung',darlehen:'fields-darlehen',wartung:'fields-wartung'};
function showTypeFields() {
    var val = document.getElementById('contract_type_sel').value;
    Object.values(typeMap).forEach(function(id){ var el=document.getElementById(id); if(el) el.style.display='none'; });
    if (typeMap[val]) { var el=document.getElementById(typeMap[val]); if(el) el.style.display=''; }
}
var ctSel = document.getElementById('contract_type_sel');
if (ctSel) { ctSel.addEventListener('change', showTypeFields); showTypeFields(); }

// Kontaktdaten-Block immer sichtbar

function addPerson() {
    var tpl = '<div class="person-row" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:.75rem;margin-bottom:.75rem;">'+
        '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-bottom:.5rem;">'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.person.role'); ?></label><input type="text" name="person_role[]" placeholder="<?php echo __('cf.ph.person_role'); ?>"></div>'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.first_name'); ?></label><input type="text" name="person_first_name[]"></div>'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.last_name'); ?></label><input type="text" name="person_last_name[]"></div></div>'+
        '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;margin-bottom:.5rem;">'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.email'); ?></label><input type="email" name="person_email[]"></div>'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.phone'); ?></label><input type="text" name="person_phone[]"></div>'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.mobile'); ?></label><input type="text" name="person_mobile[]"></div></div>'+
        '<div style="display:grid;grid-template-columns:1fr auto;gap:.5rem;align-items:end;">'+
        '<div class="form-group" style="margin:0;"><label style="font-size:.7rem;"><?php echo __('cf.person.notes'); ?></label><input type="text" name="person_notes[]"></div>'+
        '<button type="button" onclick="this.closest(\'.person-row\').remove()" class="btn btn-outline" style="color:#f87171;border-color:#f87171;padding:.35rem .6rem;"><i class="ti ti-trash"></i></button></div></div>';
    var div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('persons-list').appendChild(div.firstChild);
}

function addCustomField() {
    var row = document.createElement('div');
    row.className = 'custom-field-row';
    row.style.cssText = 'display:grid;grid-template-columns:2fr 3fr 1fr auto;gap:.5rem;margin-bottom:.5rem;align-items:center;';
    row.innerHTML = '<input type="text" name="custom_labels[]" placeholder="' + window.cf_i18n.custom_label_ph + '"><input type="text" name="custom_values[]" placeholder="' + window.cf_i18n.custom_value_ph + '"><select name="custom_types[]" onchange="syncInputType(this)"><option value="text">Text</option><option value="number">Zahl</option><option value="date">Datum</option><option value="url">URL</option><option value="email">E-Mail</option></select><button type="button" onclick="this.closest(\'.custom-field-row\').remove()" style="background:none;border:none;color:var(--danger,#e74c3c);cursor:pointer;font-size:1.1rem;">✕</button>';
    document.getElementById('custom-fields-list').appendChild(row);
}

function syncInputType(sel) {
    var input = sel.closest('.custom-field-row').querySelector('input[name="custom_values[]"]');
    input.type = ['number','date','url','email'].indexOf(sel.value) !== -1 ? sel.value : 'text';
}

// File input label im Edit-Dokumente-Tab
var editFileInput = document.getElementById("doc-file-input-edit");
if (editFileInput) {
    editFileInput.addEventListener("change", function(){
        var name = this.files[0] ? this.files[0].name : "<?php echo __('cf.doc.no_file'); ?>";
        document.getElementById("doc-file-label-edit").innerHTML = '<i class="ti ti-paperclip" style="margin-right:.3rem;"></i>' + name;
    });
}

// Initial korrekten Tab anzeigen
switchTab(activeTab);
</script>