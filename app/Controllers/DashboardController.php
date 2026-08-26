<?php
$user = currentUser();
$db   = db();

$ids = allowedClientIds();
$in  = $ids ? implode(',', array_map('intval', $ids)) : '0';

$totalContracts  = $db->query("SELECT COUNT(*) FROM contracts WHERE client_id IN ($in)")->fetchColumn();
$activeContracts  = $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND client_id IN ($in)")->fetchColumn();
$activeExpenses  = $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id IN ($in)")->fetchColumn();
$activeIncome    = $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id IN ($in)")->fetchColumn();
$totalExpenses   = $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id IN ($in)")->fetchColumn();
$totalIncome     = $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id IN ($in)")->fetchColumn();
$expiringSoon    = $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline BETWEEN date('now') AND date('now', '+30 days') AND status = 'aktiv' AND client_id IN ($in)")->fetchColumn();
$overdue         = $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline < date('now') AND status = 'aktiv' AND client_id IN ($in)")->fetchColumn();

$deadlines = $db->query("
    SELECT c.title, c.cancellation_deadline AS notice_date, cl.name AS client_name,
           CAST((julianday(c.cancellation_deadline) - julianday('now')) AS INTEGER) AS days_left
    FROM contracts c
    LEFT JOIN clients cl ON c.client_id = cl.id
    WHERE c.cancellation_deadline >= date('now')
      AND c.status = 'aktiv'
      AND c.client_id IN ($in)
    ORDER BY c.cancellation_deadline ASC
    LIMIT 5
")->fetchAll();

// Zuletzt angesehen aus DB
$stmt = $db->prepare("
    SELECT c.*, cl.name AS client_name, cc.name AS category_name
    FROM recently_viewed rv
    JOIN contracts c ON c.id = rv.contract_id
    LEFT JOIN clients cl ON c.client_id = cl.id
    LEFT JOIN contract_categories cc ON c.category_id = cc.id
    WHERE rv.user_id = ? AND c.client_id IN ($in)
    ORDER BY rv.viewed_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION["user_id"]]);
$recentContracts = $stmt->fetchAll();


// Monatliche Kosten nach Mandant
$costRows = $db->query("
    SELECT c.title, c.value, c.billing_interval, c.client_id, c.direction
    FROM contracts c
    WHERE c.status = 'aktiv'
      AND c.billing_interval IN ('monatlich', 'jaehrlich')
      AND c.value IS NOT NULL AND c.value > 0
      AND c.client_id IN ($in)
    ORDER BY c.client_id, c.direction, c.value DESC
")->fetchAll();

$costByClient = [];
foreach ($costRows as $row) {
    $monthly = $row['billing_interval'] === 'jaehrlich'
        ? round($row['value'] / 12, 2)
        : round($row['value'], 2);
    $cid = $row['client_id'];
    $dir = $row['direction'];
    if (!isset($costByClient[$cid])) $costByClient[$cid] = ['einnahme' => ['total' => 0, 'rows' => []], 'ausgabe' => ['total' => 0, 'rows' => []]];
    $costByClient[$cid][$dir]['total'] += $monthly;
    $costByClient[$cid][$dir]['rows'][] = ['title' => $row['title'], 'monthly' => $monthly];
}

// Mandantennamen laden
$clientNames = [];
if ($ids) {
    $stmt = $db->query("SELECT id, name FROM clients WHERE active=1 AND id IN ($in)");
    foreach ($stmt->fetchAll() as $cl) $clientNames[$cl['id']] = $cl['name'];
}

// Tabs: Kennzahlen pro Mandant
$tabs = [];
foreach (array_keys($clientNames) as $cid) {
    $cid = (int)$cid;
    $tabs[$cid] = [
        'name'            => $clientNames[$cid],
        'totalContracts'  => $db->query("SELECT COUNT(*) FROM contracts WHERE client_id = $cid")->fetchColumn(),
        'activeContracts' => $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND client_id = $cid")->fetchColumn(),
        'activeExpenses'  => $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id = $cid")->fetchColumn(),
        'activeIncome'    => $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id = $cid")->fetchColumn(),
        'totalExpenses'   => $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id = $cid")->fetchColumn(),
        'totalIncome'     => $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id = $cid")->fetchColumn(),
        'expiringSoon'    => $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline BETWEEN date('now') AND date('now', '+30 days') AND status = 'aktiv' AND client_id = $cid")->fetchColumn(),
        'overdue'         => $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline < date('now') AND status = 'aktiv' AND client_id = $cid")->fetchColumn(),
        'deadlines'       => $db->query("SELECT c.title, c.cancellation_deadline AS notice_date, CAST((julianday(c.cancellation_deadline) - julianday('now')) AS INTEGER) AS days_left FROM contracts c WHERE c.cancellation_deadline >= date('now') AND c.status = 'aktiv' AND c.client_id = $cid ORDER BY c.cancellation_deadline ASC LIMIT 5")->fetchAll(),
        'costByDir'       => [],
    ];
}
// Monatliche Kosten pro Mandant-Tab
foreach ($costRows as $row) {
    $cid = (int)$row['client_id'];
    if (!isset($tabs[$cid])) continue;
    $monthly = $row['billing_interval'] === 'jaehrlich' ? round($row['value'] / 12, 2) : round($row['value'], 2);
    $dir = $row['direction'];
    if (!isset($tabs[$cid]['costByDir'][$dir])) $tabs[$cid]['costByDir'][$dir] = ['total' => 0, 'rows' => []];
    $tabs[$cid]['costByDir'][$dir]['total'] += $monthly;
    $tabs[$cid]['costByDir'][$dir]['rows'][] = ['title' => $row['title'], 'monthly' => $monthly];
}

require __DIR__ . '/../Views/layouts/main.php';
require __DIR__ . '/../Views/dashboard/index.php';
require __DIR__ . '/../Views/layouts/footer.php';
