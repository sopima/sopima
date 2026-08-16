<?php $days_before = explode(',', $settings['email']['days_before'] ?? '7,30'); ?>

<div class="page-header">
    <h2><?php echo __('notifications.title'); ?></h2>
</div>

<?php if ($saved): ?>
<div class="alert alert-success"><i class="ti ti-check"></i> <?php echo __('notifications.saved'); ?></div>
<?php endif; ?>

<form method="POST" action="/notifications">
    <div class="card" style="padding:1.5rem;margin-bottom:1rem;">
        <h3 style="font-size:.88rem;font-weight:600;color:rgba(255,255,255,.9);margin-bottom:1rem;"><?php echo __('notifications.notify_me'); ?></h3>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;">
            <?php foreach ([7,14,30,60,90] as $d): ?>
            <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.88rem;color:var(--text-muted);background:rgba(255,255,255,.05);padding:.4rem .8rem;border-radius:8px;border:1px solid rgba(255,255,255,.1);">
                <input type="checkbox" name="days_before[]" value="<?php echo $d; ?>" <?php echo in_array($d, $days_before) ? 'checked' : ''; ?> style="width:auto;">
                <?php echo $d; ?> <?php echo __('notifications.days_before'); ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    $channelConfig = [
        'email'    => ['label' => 'E-Mail',    'icon' => 'ti-mail',          'fields' => [['key' => 'address',    'label' => __('notifications.field.email'), 'type' => 'email']]],
        'discord'  => ['label' => 'Discord',   'icon' => 'ti-brand-discord', 'fields' => [['key' => 'webhook_url','label' => __('notifications.field.webhook_url'),    'type' => 'url']]],
        'telegram' => ['label' => 'Telegram',  'icon' => 'ti-brand-telegram','fields' => [['key' => 'bot_token', 'label' => __('notifications.field.bot_token'),       'type' => 'text'],['key' => 'chat_id','label' => __('notifications.field.chat_id'),'type' => 'text']]],
        'ntfy'     => ['label' => 'Ntfy',      'icon' => 'ti-terminal',      'fields' => [['key' => 'url',       'label' => __('notifications.field.url'),      'type' => 'url'],['key' => 'topic','label' => __('notifications.field.topic'),'type' => 'text']]],
        'gotify'   => ['label' => 'Gotify',    'icon' => 'ti-bell',          'fields' => [['key' => 'url',       'label' => __('notifications.field.url'),      'type' => 'url'],['key' => 'token','label' => __('notifications.field.token'),'type' => 'text']]],
        'pushover' => ['label' => 'Pushover',  'icon' => 'ti-device-mobile', 'fields' => [['key' => 'user_key', 'label' => __('notifications.field.user_key'),         'type' => 'text'],['key' => 'api_token','label' => __('notifications.field.api_token'),'type' => 'text']]],
        'webhook'  => ['label' => 'Webhook',   'icon' => 'ti-webhook',       'fields' => [['key' => 'url',       'label' => __('notifications.field.webhook_url'),     'type' => 'url']]],
    ];
    foreach ($channelConfig as $channel => $cfg):
        $s       = $settings[$channel] ?? ['enabled' => 0, 'config' => []];
        $enabled = $s['enabled'];
        $config  = $s['config'];
    ?>
    <div class="card" style="margin-bottom:.75rem;overflow:visible;">
        <div class="card-head" style="cursor:pointer;" onclick="toggleChannel('<?php echo $channel; ?>')">
            <span><i class="ti <?php echo $cfg['icon']; ?>" style="margin-right:.5rem;"></i><?php echo $cfg['label']; ?></span>
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;" onclick="event.stopPropagation()">
                <input type="checkbox" name="enabled[<?php echo $channel; ?>]" value="1"
                    <?php echo $enabled ? 'checked' : ''; ?>
                    onchange="toggleChannel('<?php echo $channel; ?>')"
                    style="width:auto;">
                <span style="font-size:.78rem;color:var(--text-muted);"><?php echo $enabled ? __('notifications.active') : __('notifications.inactive'); ?></span>
            </label>
        </div>
        <div id="channel-<?php echo $channel; ?>" style="<?php echo $enabled ? '' : 'display:none;'; ?>padding:1rem 1.1rem;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;">
                <?php foreach ($cfg['fields'] as $field): ?>
                <div class="form-group" style="margin:0;">
                    <label><?php echo $field['label']; ?></label>
                    <input type="<?php echo $field['type']; ?>"
                           name="config[<?php echo $channel; ?>][<?php echo $field['key']; ?>]"
                           value="<?php echo htmlspecialchars($config[$field['key']] ?? ''); ?>"
                           placeholder="<?php echo $field['label']; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:.75rem;">
                <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:var(--text-muted);font-size:.8rem;padding:.3rem .8rem;cursor:pointer;" onclick="testChannel('<?php echo $channel; ?>')">
                    <i class="ti ti-send" style="margin-right:.3rem;"></i>Testnachricht senden
                </button>
                <span id="test-result-<?php echo $channel; ?>" style="margin-left:.75rem;font-size:.8rem;"></span>
            </div>

        </div>
    </div>
    <?php endforeach; ?>

    <div style="display:flex;justify-content:flex-end;margin-top:1rem;">
        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy"></i><?php echo __('notifications.save'); ?></button>
    </div>
</form>

<script>
window.sopima_i18n = {
    sending: <?php echo json_encode(__('notifications.sending')); ?>,
    conn_error: <?php echo json_encode(__('notifications.conn_error')); ?>
};
</script>
<script>
function toggleChannel(channel) {
    const box = document.getElementById('channel-' + channel);
    const cb  = document.querySelector('input[name="enabled[' + channel + ']"]');
    box.style.display = cb.checked ? '' : 'none';
}
function testChannel(channel) {
    const result = document.getElementById('test-result-' + channel);
    result.style.color = 'var(--text-muted)';
    result.textContent = window.sopima_i18n.sending;
    fetch('/notifications?action=test', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'channel=' + encodeURIComponent(channel)
    })
    .then(r => r.json())
    .then(data => {
        result.style.color = data.ok ? '#4ade80' : '#f87171';
        result.textContent = data.msg;
        setTimeout(() => { result.textContent = ''; }, 5000);
    })
    .catch(() => {
        result.style.color = '#f87171';
        result.textContent = window.sopima_i18n.conn_error;
    });
}
</script>