-- Kündigung Mobilfunk/Internet
INSERT OR IGNORE INTO letter_templates (client_id, name, letter_type, subject, body_html, is_default)
SELECT 0, 'Kündigung Mobilfunk/Internet', 'kuendigung', 'Kündigung – Kundennr. {{customer_number}} – Rufnummer {{phone_number}}',
'<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #111; margin: 0; padding: 0; }
  .page { padding: 2.5cm 2.5cm 2cm 2.5cm; }
  .sender { font-size: 9pt; color: #555; margin-bottom: 2cm; }
  .recipient { margin-bottom: 1.5cm; line-height: 1.6; min-height: 2cm; }
  .date { text-align: right; margin-bottom: 1.5cm; }
  .subject { font-weight: bold; margin-bottom: 1cm; }
  .body { line-height: 1.8; }
  .signature { margin-top: 2cm; }
</style>
</head>
<body>
<div class="page">
  <div class="sender">{{client_name}} · {{client_address}} · {{client_zip}} {{client_city}}</div>
  <div class="recipient">{{partner_company}}<br>{{partner_street}}<br>{{partner_zip}} {{partner_city}}</div>
  <div class="date">{{client_city}}, {{today}}</div>
  <div class="subject">Kündigung – Kundennummer {{customer_number}} – Rufnummer {{phone_number}}</div>
  <div class="body">
    <p>Sehr geehrte Damen und Herren,</p>
    <p>hiermit kündige ich meinen Mobilfunkvertrag mit der Rufnummer <strong>{{phone_number}}</strong> (Kundennummer: <strong>{{customer_number}}</strong>) fristgerecht zum <strong>{{cancellation_deadline}}</strong>, hilfsweise zum nächstmöglichen Zeitpunkt.</p>
    <p>Mit Wirksamwerden der Kündigung erlischt auch die Ihnen erteilte Einzugsermächtigung bzw. das SEPA-Lastschriftmandat.</p>
    <p>Bitte senden Sie mir eine schriftliche Bestätigung der Kündigung unter Angabe des genauen Beendigungszeitpunktes zu.</p>
    <p>Mit freundlichen Grüßen</p>
  </div>
  <div class="signature">
    <p>{{client_name}}</p>
  </div>
</div>
</body>
</html>',
1
WHERE NOT EXISTS (SELECT 1 FROM letter_templates WHERE name = 'Kündigung Mobilfunk/Internet' AND client_id = 0);

-- Kündigung Allgemein
INSERT OR IGNORE INTO letter_templates (client_id, name, letter_type, subject, body_html, is_default)
SELECT 0, 'Kündigung Allgemein', 'kuendigung', 'Kündigung – Vertragsnr. {{partner_contract_number}}',
'<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #111; margin: 0; padding: 0; }
  .page { padding: 2.5cm 2.5cm 2cm 2.5cm; }
  .sender { font-size: 9pt; color: #555; margin-bottom: 2cm; }
  .recipient { margin-bottom: 1.5cm; line-height: 1.6; min-height: 2cm; }
  .date { text-align: right; margin-bottom: 1.5cm; }
  .subject { font-weight: bold; margin-bottom: 1cm; }
  .body { line-height: 1.8; }
  .signature { margin-top: 2cm; }
</style>
</head>
<body>
<div class="page">
  <div class="sender">{{client_name}} · {{client_address}} · {{client_zip}} {{client_city}}</div>
  <div class="recipient">{{partner_company}}<br>{{partner_street}}<br>{{partner_zip}} {{partner_city}}</div>
  <div class="date">{{client_city}}, {{today}}</div>
  <div class="subject">Kündigung – Vertragsnummer {{partner_contract_number}}</div>
  <div class="body">
    <p>Sehr geehrte Damen und Herren,</p>
    <p>hiermit kündige ich den folgenden Vertrag fristgerecht zum <strong>{{cancellation_deadline}}</strong>, hilfsweise zum nächstmöglichen Zeitpunkt:</p>
    <p>
      <strong>Vertragspartner:</strong> {{partner}}<br>
      <strong>Vertragsnummer:</strong> {{partner_contract_number}}<br>
      <strong>Kundennummer:</strong> {{customer_number}}
    </p>
    <p>Mit Wirksamwerden der Kündigung erlischt auch die Ihnen erteilte Einzugsermächtigung bzw. das SEPA-Lastschriftmandat.</p>
    <p>Bitte senden Sie mir eine schriftliche Bestätigung der Kündigung unter Angabe des genauen Beendigungszeitpunktes zu.</p>
    <p>Mit freundlichen Grüßen</p>
  </div>
  <div class="signature">
    <p>{{client_name}}</p>
  </div>
</div>
</body>
</html>',
0
WHERE NOT EXISTS (SELECT 1 FROM letter_templates WHERE name = 'Kündigung Allgemein' AND client_id = 0);