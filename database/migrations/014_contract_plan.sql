-- Migration 014: Plan-Feld in contracts
ALTER TABLE contracts ADD COLUMN plan VARCHAR(50) NULL DEFAULT NULL;
