<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    public static function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        if (!defined('SMTP_HOST') || empty(SMTP_HOST)) {
            error_log('MailService: SMTP nicht konfiguriert.');
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = match(SMTP_ENCRYPTION) {
                'ssl'  => PHPMailer::ENCRYPTION_SMTPS,
                'none' => '',
                default => PHPMailer::ENCRYPTION_STARTTLS,
            };
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('MailService Fehler: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function wrapTemplate(string $name, string $intro, string $title, string $content, string $btnText = '', string $btnUrl = ''): string
    {
        $btn = '';
        if ($btnText && $btnUrl) {
            $btn = '<a href="' . htmlspecialchars($btnUrl) . '" style="display:inline-block;background:#0d6efd;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;margin-top:8px">' . htmlspecialchars($btnText) . '</a>';
        }

        return '<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:20px;margin:0">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)">
    <div style="background:#0d6efd;padding:24px 32px">
      <h1 style="color:#fff;margin:0;font-size:20px">' . APP_NAME . '</h1>
    </div>
    <div style="padding:32px">
      <p style="margin:0 0 16px">Hallo ' . htmlspecialchars($name) . ',</p>
      <p style="margin:0 0 24px;color:#555">' . htmlspecialchars($intro) . '</p>
      <div style="background:#f9fafb;border-left:4px solid #0d6efd;padding:16px 20px;margin-bottom:24px">
        <strong>' . htmlspecialchars($title) . '</strong><br>
        <span style="color:#555;font-size:14px">' . $content . '</span>
      </div>
      ' . $btn . '
    </div>
    <div style="padding:16px 32px;background:#f9fafb;font-size:12px;color:#999;border-top:1px solid #eee">
      Diese E-Mail wurde automatisch versandt &middot; <a href="' . APP_URL . '" style="color:#0d6efd;text-decoration:none">' . APP_NAME . '</a>
    </div>
  </div>
</body>
</html>';
    }

    public static function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        $subject = str_replace(':app', APP_NAME, __('mail.reset.subject'));
        $body    = self::wrapTemplate(
            $toName,
            __('mail.reset.intro'),
            __('mail.reset.btn'),
            __('mail.reset.hint'),
            __('mail.reset.btn'),
            $resetUrl
        );
        return self::send($toEmail, $toName, $subject, $body);
    }

    public static function sendNotification(string $toEmail, string $toName, string $title, string $message): bool
    {
        $body = self::wrapTemplate($toName, $message, $title, '');
        return self::send($toEmail, $toName, $title, $body);
    }

    public static function sendContractCreated(string $toEmail, array $contract): bool
    {
        $db = \db();
        $tpl = $db->prepare("SELECT subject, body, active FROM mail_templates WHERE event = 'contract.created' LIMIT 1");
        $tpl->execute();
        $row = $tpl->fetch();
        if (!$row || !$row['active']) return false;

        $placeholders = [
            '{{title}}',          '{{partner}}',       '{{start_date}}',
            '{{end_date}}',       '{{notice_date}}',   '{{value}}',
            '{{billing_interval}}', '{{status}}',      '{{app_name}}',
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
        ];

        $subject = str_replace($placeholders, $values, $row['subject']);
        $bodyText = str_replace($placeholders, $values, $row['body']);
        $bodyHtml = self::wrapTemplate('', '', $subject, nl2br(htmlspecialchars($bodyText)), 'Verträge ansehen', APP_URL . '/contracts');

        $attachments = [];
        try {
            require_once __DIR__ . '/PdfService.php';
            $attachments = PdfService::generateForContract($contract, $db);
        } catch (\Throwable $e) {
            error_log('PdfService Fehler: ' . $e->getMessage());
        }

        return self::sendWithAttachments($toEmail, $toEmail, $subject, $bodyHtml, $attachments);
    }

    public static function sendContractEvent(string $event, string $toEmail, array $contract): bool
    {
        $db = \db();
        $tpl = $db->prepare("SELECT subject, body, active FROM mail_templates WHERE event = ? LIMIT 1");
        $tpl->execute([$event]);
        $row = $tpl->fetch();
        if (!$row || !$row['active']) return false;
        $placeholders = [
            '{{title}}',          '{{partner}}',       '{{start_date}}',
            '{{end_date}}',       '{{notice_date}}',   '{{value}}',
            '{{billing_interval}}', '{{status}}',      '{{app_name}}',
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
        ];
        $subject  = str_replace($placeholders, $values, $row['subject']);
        $bodyText = str_replace($placeholders, $values, $row['body']);
        $bodyHtml = self::wrapTemplate('', '', $subject, nl2br(htmlspecialchars($bodyText)), 'Verträge ansehen', APP_URL . '/contracts');
        return self::sendWithAttachments($toEmail, $toEmail, $subject, $bodyHtml, []);
    }

    public static function sendWithAttachments(string $toEmail, string $toName, string $subject, string $body, array $attachments = []): bool
    {
        if (!defined('SMTP_HOST') || empty(SMTP_HOST)) {
            error_log('MailService: SMTP nicht konfiguriert.');
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = match(SMTP_ENCRYPTION) {
                'ssl'  => PHPMailer::ENCRYPTION_SMTPS,
                'none' => '',
                default => PHPMailer::ENCRYPTION_STARTTLS,
            };
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            foreach ($attachments as $att) {
                $mail->addStringAttachment($att['content'], $att['filename'], 'base64', 'application/pdf');
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('MailService Fehler: ' . $mail->ErrorInfo);
            return false;
        }
    }
}