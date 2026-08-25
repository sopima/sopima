<?php

namespace Sopima\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class LetterService
{
    private \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function getTemplates(int $clientId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM letter_templates WHERE client_id = 0 OR client_id = ? ORDER BY name ASC"
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTemplatesForUser(array $clientIds): array
    {
        if (empty($clientIds)) {
            $stmt = $this->db->query("SELECT * FROM letter_templates WHERE client_id = 0 ORDER BY name ASC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $in = implode(',', array_fill(0, count($clientIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT * FROM letter_templates WHERE client_id = 0 OR client_id IN ($in) ORDER BY name ASC"
        );
        $stmt->execute($clientIds);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTemplate(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM letter_templates WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createTemplate(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO letter_templates (client_id, name, letter_type, subject, body_html, is_default)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)($data['client_id'] ?? 0),
            $data['name'],
            $data['letter_type'] ?? 'custom',
            $data['subject'] ?? '',
            $data['body_html'] ?? '',
            (int)($data['is_default'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateTemplate(int $id, array $data, array $allowedClientIds = []): void
    {
        // Mandantencheck: nur eigene oder globale Vorlagen bearbeitbar
        $check = $this->db->prepare("SELECT client_id FROM letter_templates WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return;
        if ($row['client_id'] !== 0 && !empty($allowedClientIds) && !in_array((int)$row['client_id'], array_map('intval', $allowedClientIds))) {
            http_response_code(403);
            die('Zugriff verweigert.');
        }
        $stmt = $this->db->prepare(
            "UPDATE letter_templates SET name=?, letter_type=?, subject=?, body_html=?, is_default=?, updated_at=CURRENT_TIMESTAMP WHERE id=?"
        );
        $stmt->execute([
            $data['name'],
            $data['letter_type'] ?? 'custom',
            $data['subject'] ?? '',
            $data['body_html'] ?? '',
            (int)($data['is_default'] ?? 0),
            $id,
        ]);
    }

    public function deleteTemplate(int $id, array $allowedClientIds = []): void
    {
        // Mandantencheck: nur eigene oder globale Vorlagen löschbar
        $check = $this->db->prepare("SELECT client_id FROM letter_templates WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return;
        if ($row['client_id'] !== 0 && !empty($allowedClientIds) && !in_array((int)$row['client_id'], array_map('intval', $allowedClientIds))) {
            http_response_code(403);
            die('Zugriff verweigert.');
        }
        $stmt = $this->db->prepare("DELETE FROM letter_templates WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function renderPdf(int $contractId, int $templateId): string
    {
        // Vertragsdaten laden
        $stmt = $this->db->prepare(
            "SELECT c.*, cl.name AS client_name, cl.street AS client_address,
                    cl.zip AS client_zip, cl.city AS client_city
             FROM contracts c
             LEFT JOIN clients cl ON cl.id = c.client_id
             WHERE c.id = ?"
        );
        $stmt->execute([$contractId]);
        $contract = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$contract) {
            throw new \RuntimeException('Vertrag nicht gefunden');
        }

        $template = $this->getTemplate($templateId);
        if (!$template) {
            throw new \RuntimeException('Vorlage nicht gefunden');
        }

        $html = $this->replacePlaceholders($template['body_html'], $contract);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function replacePlaceholders(string $html, array $contract): string
    {
        $today = date('d.m.Y');
        $map = [
            '{{contract_number}}'  => $contract['contract_number'] ?? '',
            '{{external_id}}'      => $contract['external_id'] ?? '',
            '{{partner_contract_number}}' => $contract['partner_contract_number'] ?? '',
            '{{contract_ref}}'     => !empty($contract['partner_contract_number'])
                ? $contract['partner_contract_number']
                : (!empty($contract['external_id']) ? $contract['external_id'] : ''),
            '{{partner}}'          => $contract['partner'] ?? '',
            '{{contract_type}}'    => $contract['contract_type'] ?? '',
            '{{start_date}}'       => $contract['start_date'] ?? '',
            '{{end_date}}'         => $contract['end_date'] ?? '',
            '{{notice_date}}'      => $contract['notice_date'] ?? '',
            '{{monthly_cost}}'     => $contract['monthly_cost'] ?? '',
            '{{client_name}}'      => $contract['client_name'] ?? '',
            '{{client_address}}'   => $contract['client_address'] ?? '',
            '{{client_zip}}'       => $contract['client_zip'] ?? '',
            '{{client_city}}'      => $contract['client_city'] ?? '',
            '{{today}}'            => $today,
        ];
        return str_replace(array_keys($map), array_values($map), $html);
    }
}