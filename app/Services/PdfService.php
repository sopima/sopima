<?php
declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public static function render(string $htmlBody, string $title): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #222; line-height: 1.6; margin: 0; padding: 0; }
  h1 { font-size: 16pt; margin-bottom: 1em; }
  h2 { font-size: 13pt; margin-top: 1.5em; }
  p  { margin: 0 0 .75em; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 1em; }
  td, th { padding: 6px 8px; border: 1px solid #ddd; font-size: 10pt; }
  th { background: #f0f0f0; }
</style>
</head>
<body>' . $htmlBody . '</body>
</html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }


    public static function autoFormat(string $text): string
    {
        // Wenn bereits HTML-Tags vorhanden, direkt zurückgeben
        if (strip_tags($text) !== $text) {
            return $text;
        }

        $lines = explode("\n", $text);
        $html = "";
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === "") {
                continue;
            }
            // Nummerierte Überschriften: "1. Titel", "2. Titel" etc.
            if (preg_match('/^(\d+)\.\s+(.+)$/', $line, $m)) {
                $html .= "<h2>" . htmlspecialchars($m[1] . ". " . $m[2]) . "</h2>\n";
            }
            // Aufzählungspunkte
            elseif (preg_match('/^[-•]\s+(.+)$/', $line, $m)) {
                $html .= "<li>" . htmlspecialchars($m[1]) . "</li>\n";
            }
            // Normaler Text
            else {
                $html .= "<p>" . htmlspecialchars($line) . "</p>\n";
            }
        }
        return $html;
    }
    public static function replacePlaceholders(string $text, array $contract, array $client, array $contact): string
    {
        $placeholders = [
            '{{title}}',              '{{partner}}',           '{{start_date}}',
            '{{end_date}}',           '{{notice_date}}',       '{{value}}',
            '{{billing_interval}}',   '{{status}}',            '{{app_name}}',
            '{{client_name}}',        '{{client_street}}',     '{{client_zip}}',
            '{{client_city}}',        '{{client_email}}',      '{{client_phone}}',
            '{{contact_company}}',    '{{contact_first_name}}','{{contact_last_name}}',
            '{{contact_street}}',     '{{contact_zip}}',       '{{contact_city}}',
            '{{contact_email}}',      '{{contact_iban}}',      '{{contact_bank}}',
            '{{contact_bic}}',
        ];
        $values = [
            $contract['title']            ?? '–',
            $contract['partner']          ?? '–',
            $contract['start_date']       ?? '–',
            $contract['end_date']         ?? '–',
            $contract['notice_date']      ?? '–',
            isset($contract['value']) ? number_format((float)$contract['value'], 2, ',', '.') . ' €' : '–',
            $contract['billing_interval'] ?? '–',
            $contract['status']           ?? '–',
            APP_NAME,
            $client['name']               ?? '–',
            $client['street']             ?? '–',
            $client['zip']                ?? '–',
            $client['city']               ?? '–',
            $client['email']              ?? '–',
            $client['phone']              ?? '–',
            $contact['company']           ?? '–',
            $contact['first_name']        ?? '–',
            $contact['last_name']         ?? '–',
            $contact['street']            ?? '–',
            $contact['zip']               ?? '–',
            $contact['city']              ?? '–',
            $contact['email']             ?? '–',
            $contact['iban']              ?? '–',
            $contact['bank']              ?? '–',
            $contact['bic']               ?? '–',
        ];
        return str_replace($placeholders, $values, $text);
    }

    public static function generateForContract(array $contract, \PDO $db): array
    {
        $client_id = $contract['client_id'];

        $client = $db->prepare("SELECT * FROM clients WHERE id=?");
        $client->execute([$client_id]);
        $client = $client->fetch() ?: [];

        $contact = $db->prepare("SELECT * FROM contract_contacts WHERE contract_id=? LIMIT 1");
        $contact->execute([$contract['id']]);
        $contact = $contact->fetch() ?: [];

        $stmt = $db->prepare("SELECT * FROM pdf_templates WHERE client_id=? AND attach=1 AND active=1");
        $stmt->execute([$client_id]);
        $templates = $stmt->fetchAll();

        $attachments = [];
        foreach ($templates as $tpl) {
            $filename = strtolower(str_replace(' ', '_', $tpl['title'])) . '.pdf';
            if (!empty($tpl['file_path']) && file_exists($tpl['file_path'])) {
                // Hochgeladene PDF direkt verwenden
                $attachments[] = [
                    'filename' => $filename,
                    'content'  => file_get_contents($tpl['file_path']),
                ];
            } elseif (!empty($tpl['body'])) {
                // HTML-Template on-the-fly generieren
                $body = self::replacePlaceholders($tpl['body'], $contract, $client, $contact);
                $body = self::autoFormat($body);
                $attachments[] = [
                    'filename' => $filename,
                    'content'  => self::render($body, $tpl['title']),
                ];
            }
        }
        return $attachments;
    }
}
