<?php
$user = currentUser();
$db   = db();

$ids = allowedClientIds();
$in  = $ids ? implode(',', array_map('intval', $ids)) : '0';

$activeTab = isset($_GET['tab']) ? (int)$_GET['tab'] : 0;

// Globale Zahlen (Tab 0)
$totalContracts = $db->query("SELECT COUNT(*) FROM contracts WHERE client_id IN ($in)")->fetchColumn();
$activeExpenses = $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id IN ($in)")->fetchColumn();
$activeIncome   = $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id IN ($in)")->fetchColumn();
$totalExpenses  = $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id IN ($in)")->fetchColumn();
$totalIncome    = $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id IN ($in)")->fetchColumn();
$expiringSoon   = $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline BETWEEN date('now') AND date('now', '+30 days') AND status = 'aktiv' AND client_id IN ($in)")->fetchColumn();
$overdue        = $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline < date('now') AND status = 'aktiv' AND client_id IN ($in)")->fetchColumn();

$deadlines = $db->query("
    SELECT c.id, c.title, c.cancellation_deadline, c.value, c.billing_interval,
           cl.name AS client_name,
           COALESCE(NULLIF(TRIM(p.company),''), NULLIF(TRIM(p.last_name||' '||COALESCE(p.first_name,'')),' '), '–') AS partner_name,
           CAST((julianday(c.cancellation_deadline) - julianday('now')) AS INTEGER) AS days_left
    FROM contracts c
    LEFT JOIN clients cl ON c.client_id = cl.id
    LEFT JOIN contract_contacts p ON p.contract_id = c.id
    WHERE c.cancellation_deadline >= date('now')
      AND c.status = 'aktiv'
      AND c.client_id IN ($in)
    ORDER BY c.cancellation_deadline ASC
    LIMIT 10
")->fetchAll();

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

// Mandantennamen
$clientNames = [];
if ($ids) {
    $stmt = $db->query("SELECT id, name FROM clients WHERE active=1 AND id IN ($in)");
    foreach ($stmt->fetchAll() as $cl) $clientNames[$cl['id']] = $cl['name'];
}

// Tabs pro Mandant
$tabs = [];
foreach (array_keys($clientNames) as $cid) {
    $cid = (int)$cid;
    $tabIn = $cid;
    $tabs[$cid] = [
        'name'          => $clientNames[$cid],
        'totalContracts'=> $db->query("SELECT COUNT(*) FROM contracts WHERE client_id = $tabIn")->fetchColumn(),
        'activeExpenses'=> $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id = $tabIn")->fetchColumn(),
        'activeIncome'  => $db->query("SELECT COUNT(*) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id = $tabIn")->fetchColumn(),
        'totalExpenses' => $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'ausgabe' AND client_id = $tabIn")->fetchColumn(),
        'totalIncome'   => $db->query("SELECT COALESCE(SUM(CASE WHEN billing_interval = 'jaehrlich' THEN value / 12 ELSE value END), 0) FROM contracts WHERE status = 'aktiv' AND direction = 'einnahme' AND client_id = $tabIn")->fetchColumn(),
        'expiringSoon'  => $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline BETWEEN date('now') AND date('now', '+30 days') AND status = 'aktiv' AND client_id = $tabIn")->fetchColumn(),
        'overdue'       => $db->query("SELECT COUNT(*) FROM contracts WHERE cancellation_deadline < date('now') AND status = 'aktiv' AND client_id = $tabIn")->fetchColumn(),
        'deadlines'     => $db->query("
            SELECT c.id, c.title, c.cancellation_deadline, c.value, c.billing_interval,
                   COALESCE(NULLIF(TRIM(p.company),''), NULLIF(TRIM(p.last_name||' '||COALESCE(p.first_name,'')),' '), '–') AS partner_name,
                   CAST((julianday(c.cancellation_deadline) - julianday('now')) AS INTEGER) AS days_left
            FROM contracts c
            LEFT JOIN contract_contacts p ON p.contract_id = c.id
            WHERE c.cancellation_deadline >= date('now') AND c.status = 'aktiv' AND c.client_id = $tabIn
            ORDER BY c.cancellation_deadline ASC LIMIT 10
        ")->fetchAll(),
    ];
}

require __DIR__ . '/../Views/layouts/main.php';
require __DIR__ . '/../Views/dashboard/index.php';
require __DIR__ . '/../Views/layouts/footer.php';