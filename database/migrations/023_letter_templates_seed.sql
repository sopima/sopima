INSERT OR IGNORE INTO letter_templates (client_id, name, letter_type, subject, body_html, is_default)
SELECT 0, 'Kündigung', 'kuendigung', 'Kündigung des Vertrages {{contract_ref}}',
'<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #111; margin: 0; padding: 0; }
  .page { padding: 2.5cm 2.5cm 2cm 2.5cm; }
  .sender { font-size: 9pt; color: #555; margin-bottom: 2cm; }
  .recipient { margin-bottom: 1.5cm; line-height: 1.6; }
  .date { text-align: right; margin-bottom: 1.5cm; }
  .subject { font-weight: bold; margin-bottom: 1cm; }
  .body { line-height: 1.8; }
  .signature { margin-top: 2cm; }
</style>
</head>
<body>
<div class="page">
  <div class="sender">{{client_name}} · {{client_address}} · {{client_zip}} {{client_city}}</div>
  <div class="recipient">{{partner}}</div>
  <div class="date">{{client_city}}, {{today}}</div>
  <div class="subject">Kündigung des Vertrages Nr. {{contract_ref}}</div>
  <div class="body">
    <p>Sehr geehrte Damen und Herren,</p>
    <p>hiermit kündige ich den oben genannten Vertrag (Nr. {{contract_ref}}) fristgerecht zum nächstmöglichen Zeitpunkt, spätestens jedoch zum <strong>{{notice_date}}</strong>.</p>
    <p>Ich bitte Sie, mir den Eingang dieser Kündigung sowie das Datum der Vertragsbeendigung schriftlich zu bestätigen.</p>
    <p>Mit freundlichen Grüßen</p>
  </div>
  <div class="signature">
    <p>{{client_name}}</p>
  </div>
</div>
</body>
</html>',
1
WHERE NOT EXISTS (SELECT 1 FROM letter_templates WHERE name = 'Kündigung' AND client_id = 0);
