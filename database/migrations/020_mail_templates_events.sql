INSERT OR IGNORE INTO mail_templates (event, subject, body, active) VALUES
('contract.expiring', 'Vertrag läuft ab: {{title}}', 'Hallo,

der folgende Vertrag läuft in Kürze ab.

Titel:      {{title}}
Partner:    {{partner}}
Ende:       {{end_date}}
Kündigung:  {{notice_date}}
Wert:       {{value}}
Status:     {{status}}

Viele Grüße
{{app_name}}', 1),
('contract.cancelled', 'Vertrag gekündigt: {{title}}', 'Hallo,

für den folgenden Vertrag wurde eine Kündigung eingetragen.

Titel:      {{title}}
Partner:    {{partner}}
Ende:       {{end_date}}
Kündigung:  {{notice_date}}
Wert:       {{value}}

Viele Grüße
{{app_name}}', 1),
('contract.updated', 'Vertrag geändert: {{title}}', 'Hallo,

der folgende Vertrag wurde aktualisiert.

Titel:      {{title}}
Partner:    {{partner}}
Beginn:     {{start_date}}
Ende:       {{end_date}}
Wert:       {{value}}
Status:     {{status}}

Viele Grüße
{{app_name}}', 0);
