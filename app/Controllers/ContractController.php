<?php
$user = currentUser();

function calculateContractDates(?string $start_date, ?int $minimum_term_months, ?int $cancellation_period_days, ?int $renewal_interval_months): array
{
    if (!$start_date || !$minimum_term_months) {
        return ['end_date' => null, 'cancellation_deadline' => null, 'notice_date' => null];
    }

    $start = new DateTime($start_date);

    // Enddatum = Startdatum + Mindestlaufzeit
    $end = clone $start;
    $end->modify("+" . ($minimum_term_months - 1) . " months");
    // Letzter Tag des Endmonats (Mindestlaufzeit - 1, da Startmonat zählt)
    $end->modify('last day of this month');
    $end_date = $end->format('Y-m-d');
    $notice_date = $end_date;

    // Kündigungsfrist: letzter Tag des Monats, der mind. X Tage vor Enddatum liegt
    $cancellation_deadline = null;
    if ($cancellation_period_days) {
        $deadline = clone $end;
        $deadline->modify("-{$cancellation_period_days} days");
        $deadline->modify('last day of this month');
        // Wenn deadline >= end, einen Monat früher
        if ($deadline >= $end) {
            $deadline->modify('last day of previous month');
        }
        $cancellation_deadline = $deadline->format('Y-m-d');
    }

    if ($renewal_interval_months) {
        $renewal = clone $end;
        $renewal->modify('+' . $renewal_interval_months . ' months');
        $renewal->modify('last day of this month');
        $notice_date = $renewal->format('Y-m-d');
    }
    return [
        'end_date'             => $end_date,
        'cancellation_deadline' => $cancellation_deadline,
        'notice_date'          => $notice_date,
    ];
}
$db   = db();

$action = $_GET['action'] ?? 'index';
$id     = (int)($_GET['id'] ?? 0);
$ids    = allowedClientIds();
$in     = $ids ? implode(',', array_map('intval', $ids)) : '0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $getTab = $_GET['tab'] ?? '';
    if (in_array($getTab, ['kommunikation', 'kommunikation_delete']) && $id) {
        $stmt_check = $db->prepare("SELECT id FROM contracts WHERE id=? AND client_id IN ($in)");
        $stmt_check->execute([$id]);
        if (!$stmt_check->fetch()) { http_response_code(403); die('Zugriff verweigert.'); }
        if ($getTab === 'kommunikation') {
            $db->prepare("INSERT INTO contract_communication_log (contract_id, user_id, logged_at, channel, direction, subject, body) VALUES (?,?,?,?,?,?,?)")
               ->execute([
                   $id, $_SESSION['user_id'],
                   !empty($_POST['logged_at']) ? str_replace('T', ' ', $_POST['logged_at']) : date('Y-m-d H:i:s'),
                   $_POST['channel'], $_POST['direction'],
                   trim($_POST['subject']),
                   trim($_POST['body'] ?? ''),
               ]);
        } else {
            $logId = (int)($_POST['log_id'] ?? 0);
            $db->prepare("DELETE FROM contract_communication_log WHERE id=? AND contract_id=?")->execute([$logId, $id]);
        }
        header('Location: /contracts?action=view&id='.$id.'&tab=kommunikation');
        exit;
    }
    $action = $_POST['action'] ?? 'store';

    if ($action === 'store') {
        if (!clientAllowed((int)$_POST['client_id'])) {
            http_response_code(403); die('Zugriff verweigert.');
        }
        // Interne Vertragsnummer generieren
        $client_stmt = $db->prepare("SELECT name FROM clients WHERE id=?");
        $client_stmt->execute([$_POST["client_id"]]);
        $client_row = $client_stmt->fetch();
        $prefix = strtoupper(substr(preg_replace("/[^a-zA-Z]/", "", $client_row["name"] ?? "CH"), 0, 4));
        $b36 = base_convert((string)time(), 10, 36);
        $rand = bin2hex(random_bytes(1));
        $contract_number = "CH-" . $prefix . "-" . $b36 . "-" . $rand;
        $minimum_term_months     = $_POST['minimum_term_months'] ? (int)$_POST['minimum_term_months'] : null;
        $renewal_interval_months = $_POST['renewal_interval_months'] ? (int)$_POST['renewal_interval_months'] : null;
        $cancellation_period_days = $_POST['cancellation_period_days'] ? (int)$_POST['cancellation_period_days'] : null;
        $dates = calculateContractDates($_POST['start_date'] ?: null, $minimum_term_months, $cancellation_period_days, $renewal_interval_months);
        $stmt = $db->prepare("INSERT INTO contracts (contract_number, client_id, category_id, contract_type, title, partner, counterparty_type, description, start_date, end_date, auto_renewal, minimum_term_months, renewal_interval_months, cancellation_period_days, cancellation_deadline, notice_date, value, billing_interval, payment_method, iban, mandate_reference, interest_rate, loan_amount, monthly_rate, deductible, service_interval_months, status, notes, direction) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $contract_number,
            $_POST['client_id'],
            $_POST['category_id'] ?: null,
            $_POST['contract_type'] ?: null,
            $_POST['title'],
            $_POST['partner'],
            $_POST['counterparty_type'] ?: null,
            $_POST['description'],
            $_POST['start_date'] ?: null,
            $dates['end_date'],
            isset($_POST['auto_renewal']) ? 1 : 0,
            $minimum_term_months,
            $renewal_interval_months,
            $cancellation_period_days,
            $dates['cancellation_deadline'],
            $dates['notice_date'],
            $_POST['value'] ?: null,
            $_POST['billing_interval'],
            $_POST['payment_method'] ?: null,
            $_POST['iban'] ?: null,
            $_POST['mandate_reference'] ?: null,
            $_POST['interest_rate'] ?: null,
            $_POST['loan_amount'] ?: null,
            $_POST['monthly_rate'] ?: null,
            $_POST['deductible'] ?: null,
            $_POST['service_interval_months'] ?: null,
            $_POST['status'],
            $_POST['notes'],
            $_POST['direction'] ?? 'ausgabe',
        ]);
        $lastId = $db->lastInsertId();
        // Personen speichern
        $personCount = max(
            count($_POST['person_role'] ?? []),
            count($_POST['person_first_name'] ?? []),
            count($_POST['person_last_name'] ?? [])
        );
        if ($personCount > 0) {
            $pStmt = $db->prepare("INSERT INTO contract_persons (contract_id, role, first_name, last_name, email, phone, mobile, notes, sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < $personCount; $i++) {
                $fn = trim($_POST['person_first_name'][$i] ?? '');
                $ln = trim($_POST['person_last_name'][$i] ?? '');
                $ro = trim($_POST['person_role'][$i] ?? '');
                if ($fn === '' && $ln === '' && $ro === '') continue;
                $pStmt->execute([$lastId, $ro ?: null, $fn ?: null, $ln ?: null, $_POST['person_email'][$i] ?? null, $_POST['person_phone'][$i] ?? null, $_POST['person_mobile'][$i] ?? null, $_POST['person_notes'][$i] ?? null, $i]);
            }
        }
        // Custom Fields speichern
        if (!empty($_POST['custom_labels'])) {
            $cfStmt = $db->prepare("INSERT INTO contract_custom_fields (contract_id, label, value, field_type, sort_order) VALUES (?,?,?,?,?)");
            foreach ($_POST['custom_labels'] as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $cfStmt->execute([$lastId, $label, $_POST['custom_values'][$i] ?? null, $_POST['custom_types'][$i] ?? 'text', (int)$i]);
            }
        }
        // Kontaktdaten speichern (nur nicht-private Mandanten)
        $cl_stmt = $db->prepare("SELECT type FROM clients WHERE id=?");
        $cl_stmt->execute([$_POST['client_id']]);
        $cl_row = $cl_stmt->fetch();
        if ($cl_row && strtolower($cl_row['type']) !== 'privat') {
            $db->prepare("INSERT INTO contract_contacts (contract_id, company, first_name, last_name, email, phone, mobile, street, zip, city, iban, bank, bic) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE company=VALUES(company), first_name=VALUES(first_name), last_name=VALUES(last_name), email=VALUES(email), phone=VALUES(phone), mobile=VALUES(mobile), street=VALUES(street), zip=VALUES(zip), city=VALUES(city), iban=VALUES(iban), bank=VALUES(bank), bic=VALUES(bic)")
               ->execute([$lastId, $_POST['cc_company']??null, $_POST['cc_first_name']??null, $_POST['cc_last_name']??null, $_POST['cc_email']??null, $_POST['cc_phone']??null, $_POST['cc_mobile']??null, $_POST['cc_street']??null, $_POST['cc_zip']??null, $_POST['cc_city']??null, $_POST['cc_iban']??null, $_POST['cc_bank']??null, $_POST['cc_bic']??null]);
        }
        header('Location: /contracts');
        exit;
    }

    if ($action === 'update') {
        if (!clientAllowed((int)$_POST['client_id'])) {
            http_response_code(403); die('Zugriff verweigert.');
        }
        $minimum_term_months_u     = $_POST['minimum_term_months'] ? (int)$_POST['minimum_term_months'] : null;
        $renewal_interval_months_u = $_POST['renewal_interval_months'] ? (int)$_POST['renewal_interval_months'] : null;
        $cancellation_period_days_u = $_POST['cancellation_period_days'] ? (int)$_POST['cancellation_period_days'] : null;
        $dates_u = calculateContractDates($_POST['start_date'] ?: null, $minimum_term_months_u, $cancellation_period_days_u, $renewal_interval_months_u);
        $stmt = $db->prepare("UPDATE contracts SET client_id=?, category_id=?, contract_type=?, title=?, partner=?, counterparty_type=?, description=?, start_date=?, end_date=?, auto_renewal=?, minimum_term_months=?, renewal_interval_months=?, cancellation_period_days=?, cancellation_deadline=?, notice_date=?, value=?, billing_interval=?, payment_method=?, iban=?, mandate_reference=?, interest_rate=?, loan_amount=?, monthly_rate=?, deductible=?, service_interval_months=?, status=?, notes=?, direction=? WHERE id=? AND client_id IN ($in)");
        $stmt->execute([
            $_POST['client_id'],
            $_POST['category_id'] ?: null,
            $_POST['contract_type'] ?: null,
            $_POST['title'],
            $_POST['partner'],
            $_POST['counterparty_type'] ?: null,
            $_POST['description'],
            $_POST['start_date'] ?: null,
            $dates_u['end_date'],
            isset($_POST['auto_renewal']) ? 1 : 0,
            $minimum_term_months_u,
            $renewal_interval_months_u,
            $cancellation_period_days_u,
            $dates_u['cancellation_deadline'],
            $dates_u['notice_date'],
            $_POST['value'] ?: null,
            $_POST['billing_interval'],
            $_POST['payment_method'] ?: null,
            $_POST['iban'] ?: null,
            $_POST['mandate_reference'] ?: null,
            $_POST['interest_rate'] ?: null,
            $_POST['loan_amount'] ?: null,
            $_POST['monthly_rate'] ?: null,
            $_POST['deductible'] ?: null,
            $_POST['service_interval_months'] ?: null,
            $_POST['status'],
            $_POST['notes'],
            $_POST['direction'] ?? 'ausgabe',
            $_POST['id'],
        ]);
        // Personen: löschen und neu schreiben
        $db->prepare("DELETE FROM contract_persons WHERE contract_id=?")->execute([(int)$_POST['id']]);
        $personCount = max(
            count($_POST['person_role'] ?? []),
            count($_POST['person_first_name'] ?? []),
            count($_POST['person_last_name'] ?? [])
        );
        if ($personCount > 0) {
            $pStmt = $db->prepare("INSERT INTO contract_persons (contract_id, role, first_name, last_name, email, phone, mobile, notes, sort_order) VALUES (?,?,?,?,?,?,?,?,?)");
            for ($i = 0; $i < $personCount; $i++) {
                $fn = trim($_POST['person_first_name'][$i] ?? '');
                $ln = trim($_POST['person_last_name'][$i] ?? '');
                $ro = trim($_POST['person_role'][$i] ?? '');
                if ($fn === '' && $ln === '' && $ro === '') continue;
                $pStmt->execute([(int)$_POST['id'], $ro ?: null, $fn ?: null, $ln ?: null, $_POST['person_email'][$i] ?? null, $_POST['person_phone'][$i] ?? null, $_POST['person_mobile'][$i] ?? null, $_POST['person_notes'][$i] ?? null, $i]);
            }
        }
        // Custom Fields: löschen und neu schreiben
        $db->prepare("DELETE FROM contract_custom_fields WHERE contract_id=?")->execute([(int)$_POST['id']]);
        if (!empty($_POST['custom_labels'])) {
            $cfStmt = $db->prepare("INSERT INTO contract_custom_fields (contract_id, label, value, field_type, sort_order) VALUES (?,?,?,?,?)");
            foreach ($_POST['custom_labels'] as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $cfStmt->execute([(int)$_POST['id'], $label, $_POST['custom_values'][$i] ?? null, $_POST['custom_types'][$i] ?? 'text', (int)$i]);
            }
        }
        // Kontaktdaten speichern (nur nicht-private Mandanten)
        $cl_stmt2 = $db->prepare("SELECT type FROM clients WHERE id=?");
        $cl_stmt2->execute([$_POST['client_id']]);
        $cl_row2 = $cl_stmt2->fetch();
        if ($cl_row2 && strtolower($cl_row2['type']) !== 'privat') {
            $db->prepare("INSERT INTO contract_contacts (contract_id, company, first_name, last_name, email, phone, mobile, street, zip, city, iban, bank, bic) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE company=VALUES(company), first_name=VALUES(first_name), last_name=VALUES(last_name), email=VALUES(email), phone=VALUES(phone), mobile=VALUES(mobile), street=VALUES(street), zip=VALUES(zip), city=VALUES(city), iban=VALUES(iban), bank=VALUES(bank), bic=VALUES(bic)")
               ->execute([(int)$_POST['id'], $_POST['cc_company']??null, $_POST['cc_first_name']??null, $_POST['cc_last_name']??null, $_POST['cc_email']??null, $_POST['cc_phone']??null, $_POST['cc_mobile']??null, $_POST['cc_street']??null, $_POST['cc_zip']??null, $_POST['cc_city']??null, $_POST['cc_iban']??null, $_POST['cc_bank']??null, $_POST['cc_bic']??null]);
        }
        header('Location: /contracts');
        exit;
    }


    if ($action === 'upload') {
        $contractId = (int)$_POST['contract_id'];
        $stmt = $db->prepare("SELECT * FROM contracts WHERE id=? AND client_id IN ($in)");
        $stmt->execute([$contractId]);
        $contract = $stmt->fetch();
        if (!$contract) { http_response_code(403); die('Zugriff verweigert.'); }

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $redir_base2 = !empty($_POST['from_edit']) ? "/contracts?action=edit&id=$contractId" : "/contracts?action=view&id=$contractId";
        header("Location: $redir_base2&tab=dokumente&upload_error=1");
            exit;
        }

        $allowed = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'image/jpeg','image/png','image/gif','image/webp','text/plain'];
        $mime = mime_content_type($_FILES['document']['tmp_name']);
        if (!in_array($mime, $allowed)) {
            $redir_base3 = !empty($_POST['from_edit']) ? "/contracts?action=edit&id=$contractId" : "/contracts?action=view&id=$contractId";
        header("Location: $redir_base3&tab=dokumente&upload_error=2");
            exit;
        }

        $maxSize = 20 * 1024 * 1024; // 20MB
        if ($_FILES['document']['size'] > $maxSize) {
            $redir_base4 = !empty($_POST['from_edit']) ? "/contracts?action=edit&id=$contractId" : "/contracts?action=view&id=$contractId";
        header("Location: $redir_base4&tab=dokumente&upload_error=3");
            exit;
        }

        $dir = __DIR__ . '/../../storage/uploads/contracts/' . $contract['client_id'] . '/' . $contractId . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('doc_', true) . '.' . strtolower($ext);
        move_uploaded_file($_FILES['document']['tmp_name'], $dir . $filename);

        $label = trim($_POST['label'] ?? '');
        $db->prepare("INSERT INTO contract_documents (contract_id, client_id, user_id, filename, original_name, mime_type, filesize, label) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$contractId, $contract['client_id'], $user['id'], $filename, $_FILES['document']['name'], $mime, $_FILES['document']['size'], $label ?: null]);

        $redir_base = !empty($_POST['from_edit']) ? "/contracts?action=edit&id=$contractId" : "/contracts?action=view&id=$contractId";
        header("Location: $redir_base&tab=dokumente&uploaded=1");
        exit;
    }

    if ($action === 'delete_doc') {
        $docId = (int)$_POST['doc_id'];
        $stmt = $db->prepare("SELECT cd.*, c.client_id FROM contract_documents cd JOIN contracts c ON c.id=cd.contract_id WHERE cd.id=? AND c.client_id IN ($in)");
        $stmt->execute([$docId]);
        $doc = $stmt->fetch();
        if (!$doc) { http_response_code(403); die('Zugriff verweigert.'); }

        $path = __DIR__ . '/../../storage/uploads/contracts/' . $doc['client_id'] . '/' . $doc['contract_id'] . '/' . $doc['filename'];
        if (file_exists($path)) unlink($path);
        $db->prepare("DELETE FROM contract_documents WHERE id=?")->execute([$docId]);

        $from_edit = !empty($_POST['from_edit']) ? 1 : 0;
        $redir_action = $from_edit ? "edit" : "view";
        header("Location: /contracts?action=$redir_action&id={$doc['contract_id']}&tab=dokumente&deleted_doc=1");
        exit;
    }
    if ($action === 'delete') {
        $db->prepare("DELETE FROM contracts WHERE id=? AND client_id IN ($in)")->execute([$_POST['id']]);
        header('Location: /contracts');
        exit;
    }
}

$clients    = $db->query("SELECT id, name FROM clients WHERE active=1 AND id IN ($in) ORDER BY name")->fetchAll();
$categories = $db->query("SELECT id, name, color FROM contract_categories ORDER BY name")->fetchAll();

if ($action === 'create') {
    $contract = null;
    $contact = [];
    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/contracts/form.php';
} elseif ($action === 'view' && $id) {
    $stmt = $db->prepare("SELECT c.*, cl.name AS client_name, cl.type AS client_type, cc.name AS category_name, cc.color AS category_color FROM contracts c LEFT JOIN clients cl ON c.client_id=cl.id LEFT JOIN contract_categories cc ON c.category_id=cc.id WHERE c.id=? AND c.client_id IN ($in)");
    $stmt->execute([$id]);
    $contract = $stmt->fetch();
    if (!$contract) { http_response_code(403); die('Zugriff verweigert.'); }
    // Zuletzt angesehen in DB speichern
    $db->prepare("INSERT INTO recently_viewed (user_id, contract_id) VALUES (?,?) ON DUPLICATE KEY UPDATE viewed_at=datetime('now')")->execute([$_SESSION["user_id"], $id]);
    // Nur die letzten 5 pro User behalten
    $db->prepare("DELETE FROM recently_viewed WHERE user_id=? AND contract_id NOT IN (SELECT contract_id FROM (SELECT contract_id FROM recently_viewed WHERE user_id=? ORDER BY viewed_at DESC LIMIT 5) AS t)")->execute([$_SESSION["user_id"], $_SESSION["user_id"]]);

    $cc_stmt = $db->prepare("SELECT * FROM contract_contacts WHERE contract_id=?");
    $cc_stmt->execute([$id]);
    $contact = $cc_stmt->fetch();

    $cf_view_stmt = $db->prepare("SELECT * FROM contract_custom_fields WHERE contract_id=? ORDER BY sort_order");
    $cf_view_stmt->execute([$id]);
    $custom_fields = $cf_view_stmt->fetchAll();
    $pv_stmt = $db->prepare("SELECT * FROM contract_persons WHERE contract_id=? ORDER BY sort_order");
    $pv_stmt->execute([$id]);
    $persons = $pv_stmt->fetchAll();

    $docs = $db->prepare("SELECT cd.*, u.name AS uploader FROM contract_documents cd LEFT JOIN users u ON u.id=cd.user_id WHERE cd.contract_id=? ORDER BY cd.uploaded_at DESC");
    $docs->execute([$id]);
    $documents = $docs->fetchAll();

    // Communication Log laden
    $log_stmt = $db->prepare("SELECT cl.*, u.name AS user_name FROM contract_communication_log cl LEFT JOIN users u ON u.id=cl.user_id WHERE cl.contract_id=? ORDER BY cl.logged_at DESC");
    $log_stmt->execute([$id]);
    $comm_log = $log_stmt->fetchAll();

    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/contracts/detail.php';
} elseif ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM contracts WHERE id=? AND client_id IN ($in)");
    $stmt->execute([$id]);
    $contract = $stmt->fetch();
    if (!$contract) { http_response_code(403); die('Zugriff verweigert.'); }
    $cc_stmt = $db->prepare("SELECT * FROM contract_contacts WHERE contract_id=?");
    $cc_stmt->execute([$id]);
    $contact = $cc_stmt->fetch() ?: [];
    $cf_stmt = $db->prepare("SELECT * FROM contract_custom_fields WHERE contract_id=? ORDER BY sort_order");
    $cf_stmt->execute([$id]);
    $custom_fields = $cf_stmt->fetchAll();
    $p_stmt = $db->prepare("SELECT * FROM contract_persons WHERE contract_id=? ORDER BY sort_order");
    $p_stmt->execute([$id]);
    $persons = $p_stmt->fetchAll();
    $docs_stmt = $db->prepare("SELECT cd.*, u.name AS uploader FROM contract_documents cd LEFT JOIN users u ON u.id=cd.user_id WHERE cd.contract_id=? ORDER BY cd.uploaded_at DESC");
    $docs_stmt->execute([$id]);
    $documents = $docs_stmt->fetchAll();
    $uploadError = $_GET['upload_error'] ?? '';
    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/contracts/form.php';
} elseif ($action === 'download' && $id) {
    $docId = $id;
    $stmt = $db->prepare("SELECT cd.*, c.client_id FROM contract_documents cd JOIN contracts c ON c.id=cd.contract_id WHERE cd.id=? AND c.client_id IN ($in)");
    $stmt->execute([$docId]);
    $doc = $stmt->fetch();
    if (!$doc) { http_response_code(403); die('Zugriff verweigert.'); }

    $path = __DIR__ . '/../../storage/uploads/contracts/' . $doc['client_id'] . '/' . $doc['contract_id'] . '/' . $doc['filename'];
    if (!file_exists($path)) { http_response_code(404); die('Datei nicht gefunden.'); }

    header('Content-Type: ' . $doc['mime_type']);
    header('Content-Disposition: attachment; filename="' . $doc['original_name'] . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
} else {
    $filter_client = (int)($_GET['client_id'] ?? 0);
    $filter_status = $_GET['status'] ?? '';
    $filter_search = trim($_GET["q"] ?? "");

    $where  = ["c.client_id IN ($in)"];
    $params = [];
    if ($filter_client && clientAllowed($filter_client)) { $where[] = 'c.client_id = ?'; $params[] = $filter_client; }
    if ($filter_status) { $where[] = 'c.status = ?'; $params[] = $filter_status; }
    if ($filter_search) { $where[] = "(c.title LIKE ? OR c.partner LIKE ? OR c.description LIKE ? OR c.notes LIKE ? OR c.contract_number LIKE ?)"; $s = "%" . $filter_search . "%"; array_push($params, $s, $s, $s, $s, $s); }

    $sql = "SELECT c.*, cl.name AS client_name, cc.name AS category_name, cc.color AS category_color
            FROM contracts c
            LEFT JOIN clients cl ON c.client_id = cl.id
            LEFT JOIN contract_categories cc ON c.category_id = cc.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY c.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $contracts = $stmt->fetchAll();

    require __DIR__ . '/../Views/layouts/main.php';
    require __DIR__ . '/../Views/contracts/index.php';
}

require __DIR__ . '/../Views/layouts/footer.php';
