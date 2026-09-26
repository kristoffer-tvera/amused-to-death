-- Adds characters.owner_id: the Blizzard account identifier (the OAuth "sub",
-- a stable numeric account id) that owns a character. This is the durable
-- ownership key for the Battle.net login pivot — the BattleTag can change, the
-- account sub does not.
--
-- The legacy "discord" column is kept for now (existing rows are still linked by
-- Discord username); owner_id is populated as accounts log in via Battle.net and
-- claim/import their characters.
--
-- IF NOT EXISTS keeps this safe to run against a database where it was already
-- applied out of band.

ALTER TABLE characters ADD COLUMN IF NOT EXISTS owner_id varchar(64);
