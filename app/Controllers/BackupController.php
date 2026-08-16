<?php
$user   = currentUser();
$db     = db();
$action = $_GET['action'] ?? 'index';

// Export: JSON
if ($action === 'export-json' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = collectBackupData($db, $user);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $filename = 'sopima-backup-' . date('Y-m-d_His') . '.json';
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($json));
    echo $json;
    exit;
}

// Export: CSV-ZIP
if ($action === 'export-csv' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = collectBackupData($db, $user);
    $tmpDir = sys_get_temp_dir() . '/ch_export_' . uniqid();
    mkdir($tmpDir);

    foreach ($data as $table => $rows) {
        if (empty($rows) || !is_array($rows) || !isset($rows[0]) || !is_array($rows[0])) continue;
        $fp = fopen($tmpDir . '/' . $table . '.csv', 'w');
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, array_keys($rows[0]), ';');
        foreach ($rows as $row) {
            fputcsv($fp, array_map(fn($v) => $v ?? '', $row), ';');
        }
        fclose($fp);
    }

    $zipFile = sys_get_temp_dir() . '/sopima-backup-' . date('Y-m-d_His') . '.zip';
    $zip = new ZipArchive();
    $zip->open($zipFile, ZipArchive::CREATE);
    foreach (glob($tmpDir . '/*.csv') as $csv) {
        $zip->addFile($csv, basename($csv));
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);

    foreach (glob($tmpDir . '/*.csv') as $f) unlink($f);
    rmdir($tmpDir);
    unlink($zipFile);
    exit;
}

// Import: JSON
if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if (empty($_FILES['backup_file']['tmp_name'])) {
        $errors[] = 'Keine Datei hochgeladen.';
    } else {
        $json = file_get_contents($_FILES['backup_file']['tmp_name']);
        $data = json_decode($json, true);
        if (!$data || !isset($data['meta']['version'])) {
            $errors[] = __('backup.error.invalid');
        }
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $clientIds = $db->prepare("SELECT client_id FROM user_clients WHERE user_id=?");
            $clientIds->execute([$user['id']]);
            $ids = array_column($clientIds->fetchAll(PDO::FETCH_ASSOC), 'client_id');

            foreach ($ids as $cid) {
                $db->prepare("DELETE FROM contracts WHERE client_id=?")->execute([$cid]);
            }

            foreach ($data['contracts'] ?? [] as $contract) {
                $contractId = insertContract($db, $contract);

                foreach ($contract['persons'] ?? [] as $p) {
                    $db->prepare("INSERT INTO contract_persons
                                  (contract_id, role, first_name, last_name, email, phone, mobile, notes, sort_order)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                       ->execute([
                           $contractId, $p['role'] ?? null, $p['first_name'] ?? null,
                           $p['last_name'] ?? null, $p['email'] ?? null,
                           $p['phone'] ?? null, $p['mobile'] ?? null,
                           $p['notes'] ?? null, $p['sort_order'] ?? 0,
                       ]);
                }

                foreach ($contract['custom_fields'] ?? [] as $cf) {
                    $db->prepare("INSERT INTO contract_custom_fields (contract_id, label, value, field_type, sort_order)
                                  VALUES (?, ?, ?, ?, ?)")
                       ->execute([
                           $contractId, $cf['label'], $cf['value'] ?? null,
                           $cf['field_type'] ?? 'text', $cf['sort_order'] ?? 0,
                       ]);
                }

                foreach ($contract['documents'] ?? [] as $doc) {
                    $db->prepare("INSERT INTO contract_documents
                                  (contract_id, client_id, user_id, filename, original_name, mime_type, filesize, label)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                       ->execute([
                           $contractId, $doc['client_id'], $doc['user_id'],
                           $doc['filename'], $doc['original_name'],
                           $doc['mime_type'], $doc['filesize'], $doc['label'] ?? null,
                       ]);
                }

                if (!empty($contract['contact'])) {
                    $co = $contract['contact'];
                    $db->prepare("INSERT INTO contract_contacts
                                  (contract_id, company, first_name, last_name, email, phone, mobile, street, zip, city, iban, bank, bic)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                       ->execute([
                           $contractId, $co['company'] ?? null, $co['first_name'] ?? null,
                           $co['last_name'] ?? null, $co['email'] ?? null,
                           $co['phone'] ?? null, $co['mobile'] ?? null,
                           $co['street'] ?? null, $co['zip'] ?? null, $co['city'] ?? null,
                           $co['iban'] ?? null, $co['bank'] ?? null, $co['bic'] ?? null,
                       ]);
                }
            }

            foreach ($data['users'] ?? [] as $u) {
                $exists = $db->prepare("SELECT id FROM users WHERE email=?");
                $exists->execute([$u['email']]);
                if (!$exists->fetch()) {
                    $db->prepare("INSERT INTO users (name, email, password_hash, role, created_at)
                                  VALUES (?, ?, ?, ?, ?)")
                       ->execute([
                           $u['name'], $u['email'],
                           password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                           $u['role'], $u['created_at'],
                       ]);
                }
            }

            $db->commit();
            $success = __('backup.success.import');
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Fehler beim Import: ' . $e->getMessage();
        }
    }
}

function collectBackupData(PDO $db, array $user): array {
    $clientIds = $db->prepare("SELECT client_id FROM user_clients WHERE user_id=?");
    $clientIds->execute([$user['id']]);
    $ids = array_column($clientIds->fetchAll(PDO::FETCH_ASSOC), 'client_id');

    if (empty($ids)) {
        return ['meta' => ['version' => '1.0', 'app' => APP_NAME, 'exported_at' => date('c')],
                'clients' => [], 'users' => [], 'contracts' => []];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $clients = $db->prepare("SELECT id, name, type, description, created_at FROM clients WHERE id IN ($placeholders)");
    $clients->execute($ids);
    $clientsData = $clients->fetchAll(PDO::FETCH_ASSOC);

    $users = $db->query("SELECT id, name, email, role, created_at FROM users")->fetchAll(PDO::FETCH_ASSOC);

    $contracts = $db->prepare("SELECT * FROM contracts WHERE client_id IN ($placeholders)");
    $contracts->execute($ids);
    $contractsData = $contracts->fetchAll(PDO::FETCH_ASSOC);

    foreach ($contractsData as &$contract) {
        $id = $contract['id'];

        $p = $db->prepare("SELECT role, first_name, last_name, email, phone, mobile, notes, sort_order FROM contract_persons WHERE contract_id=? ORDER BY sort_order");
        $p->execute([$id]);
        $contract['persons'] = $p->fetchAll(PDO::FETCH_ASSOC);

        $cf = $db->prepare("SELECT label, value, field_type, sort_order FROM contract_custom_fields WHERE contract_id=? ORDER BY sort_order");
        $cf->execute([$id]);
        $contract['custom_fields'] = $cf->fetchAll(PDO::FETCH_ASSOC);

        $doc = $db->prepare("SELECT client_id, user_id, filename, original_name, mime_type, filesize, label FROM contract_documents WHERE contract_id=?");
        $doc->execute([$id]);
        $contract['documents'] = $doc->fetchAll(PDO::FETCH_ASSOC);

        $co = $db->prepare("SELECT company, first_name, last_name, email, phone, mobile, street, zip, city, iban, bank, bic FROM contract_contacts WHERE contract_id=?");
        $co->execute([$id]);
        $contract['contact'] = $co->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    unset($contract);

    return [
        'meta' => [
            'version'     => '1.0',
            'app'         => APP_NAME,
            'exported_at' => date('c'),
        ],
        'clients'   => $clientsData,
        'users'     => $users,
        'contracts' => $contractsData,
    ];
}

function insertContract(PDO $db, array $c): int {
    $db->prepare("INSERT INTO contracts (
        contract_number, client_id, category_id, contract_type, title, partner,
        counterparty_type, description, start_date, end_date, auto_renewal,
        cancellation_period_days, cancellation_deadline, notice_date,
        value, payment_method, iban, mandate_reference,
        interest_rate, loan_amount, monthly_rate, deductible,
        service_interval_months, billing_interval, status, source, external_id,
        document_path, notes, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
    ->execute([
        $c['contract_number'] ?? null, $c['client_id'], $c['category_id'] ?? null,
        $c['contract_type'] ?? null, $c['title'], $c['partner'] ?? null,
        $c['counterparty_type'] ?? null, $c['description'] ?? null,
        $c['start_date'] ?? null, $c['end_date'] ?? null, $c['auto_renewal'] ?? 0,
        $c['cancellation_period_days'] ?? null, $c['cancellation_deadline'] ?? null,
        $c['notice_date'] ?? null, $c['value'] ?? null, $c['payment_method'] ?? null,
        $c['iban'] ?? null, $c['mandate_reference'] ?? null,
        $c['interest_rate'] ?? null, $c['loan_amount'] ?? null,
        $c['monthly_rate'] ?? null, $c['deductible'] ?? null,
        $c['service_interval_months'] ?? null, $c['billing_interval'] ?? 'jaehrlich',
        $c['status'] ?? 'aktiv', $c['source'] ?? 'manuell', $c['external_id'] ?? null,
        $c['document_path'] ?? null, $c['notes'] ?? null,
        $c['created_at'], $c['updated_at'],
    ]);
    return (int)$db->lastInsertId();
}

require __DIR__ . '/../Views/layouts/main.php';
require __DIR__ . '/../Views/backup/index.php';
require __DIR__ . '/../Views/layouts/footer.php';