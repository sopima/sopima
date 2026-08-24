<?php

namespace Sopima\Controllers;

use Sopima\Services\LetterService;

class LetterController
{
    private LetterService $letterService;
    private \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->letterService = new LetterService($db);
    }

    // GET /contracts/{id}/letter – Modal-Daten (Vorlagenliste)
    public function selectTemplate(int $contractId): void
    {
        $ids = allowedClientIds();
        $templates = $this->letterService->getTemplatesForUser($ids);

        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, contract_number, partner, client_id FROM contracts WHERE id = ? AND client_id IN ($in)");
        $stmt->execute(array_merge([$contractId], $ids));
        $contract = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$contract) {
            http_response_code(404);
            echo 'Vertrag nicht gefunden';
            return;
        }

        require __DIR__ . '/../Views/letters/select_template.php';
    }

    // GET /contracts/{id}/letter/{template_id}/pdf
    public function downloadPdf(int $contractId, int $templateId): void
    {
        $ids = allowedClientIds();

        // Mandantenprüfung
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, contract_number FROM contracts WHERE id = ? AND client_id IN ($in)");
        $stmt->execute(array_merge([$contractId], $ids));
        $contract = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$contract) {
            http_response_code(403);
            echo 'Zugriff verweigert';
            return;
        }

        try {
            $pdf = $this->letterService->renderPdf($contractId, $templateId);
            $filename = 'Brief_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $contract['contract_number']) . '.pdf';

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
        } catch (\RuntimeException $e) {
            http_response_code(500);
            echo htmlspecialchars($e->getMessage());
        }
    }

    // GET /contracts/{id}/letter/{template_id}/preview
    public function previewPdf(int $contractId, int $templateId): void
    {
        $ids = allowedClientIds();
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, contract_number FROM contracts WHERE id = ? AND client_id IN ($in)");
        $stmt->execute(array_merge([$contractId], $ids));
        $contract = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$contract) {
            http_response_code(403);
            echo 'Zugriff verweigert';
            return;
        }

        try {
            $pdf = $this->letterService->renderPdf($contractId, $templateId);
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="preview.pdf"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
        } catch (\RuntimeException $e) {
            http_response_code(500);
            echo htmlspecialchars($e->getMessage());
        }
    }

    // Einstellungen: Vorlagenliste
    public function settingsIndex(): void
    {
        $ids = allowedClientIds();
        $templates = $this->letterService->getTemplatesForUser($ids);
        require __DIR__ . '/../Views/settings/letter_templates.php';
    }

    // Einstellungen: Vorlage bearbeiten
    public function settingsEdit(int $id): void
    {
        $ids = allowedClientIds();
        $template = $this->letterService->getTemplate($id);
        if (!$template) {
            http_response_code(404);
            echo 'Vorlage nicht gefunden';
            return;
        }
        $templates = $this->letterService->getTemplatesForUser($ids);
        require __DIR__ . '/../Views/settings/letter_templates.php';
    }

    // Einstellungen: Neue Vorlage speichern
    public function settingsCreate(): void
    {
        $clientId = $_SESSION['client_id'] ?? 0;
        $this->letterService->createTemplate([
            'client_id'   => $clientId,
            'name'        => $_POST['name'] ?? '',
            'letter_type' => $_POST['letter_type'] ?? 'custom',
            'subject'     => $_POST['subject'] ?? '',
            'body_html'   => $_POST['body_html'] ?? '',
            'is_default'  => isset($_POST['is_default']) ? 1 : 0,
        ]);
        header('Location: /settings/letter-templates?saved=1');
        exit;
    }

    // Einstellungen: Vorlage aktualisieren
    public function settingsUpdate(int $id): void
    {
        $this->letterService->updateTemplate($id, [
            'name'        => $_POST['name'] ?? '',
            'letter_type' => $_POST['letter_type'] ?? 'custom',
            'subject'     => $_POST['subject'] ?? '',
            'body_html'   => $_POST['body_html'] ?? '',
            'is_default'  => isset($_POST['is_default']) ? 1 : 0,
        ]);
        header('Location: /settings/letter-templates?saved=1');
        exit;
    }

    // Einstellungen: Vorlage löschen
    public function settingsDelete(int $id): void
    {
        $this->letterService->deleteTemplate($id);
        header('Location: /settings/letter-templates?deleted=1');
        exit;
    }
}