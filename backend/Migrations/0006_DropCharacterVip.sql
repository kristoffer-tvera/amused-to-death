-- Drops characters.vip. The "vip" flag was a legacy stand-in for admin status;
-- admin is now derived from guild rank at login, so the column is obsolete.

ALTER TABLE characters DROP COLUMN IF EXISTS vip;
