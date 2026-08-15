ALTER TABLE contracts
    ADD COLUMN minimum_term_months INT NULL DEFAULT NULL,
    ADD COLUMN renewal_interval_months INT NULL DEFAULT NULL;
