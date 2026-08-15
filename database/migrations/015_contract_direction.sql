ALTER TABLE contracts
    ADD COLUMN direction VARCHAR(10) NOT NULL DEFAULT 'ausgabe';

UPDATE contracts SET direction = 'einnahme' WHERE source = 'api';
