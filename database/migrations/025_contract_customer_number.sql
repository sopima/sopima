-- Migration 025: customer_number field for contracts
ALTER TABLE contracts ADD COLUMN customer_number TEXT DEFAULT NULL;
