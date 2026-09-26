-- Drops characters.discord. Discord authentication has been removed entirely;
-- character ownership is now the Blizzard account id in characters.owner_id.
-- The old Discord usernames no longer map to a login identity, so the column is
-- dead data.

ALTER TABLE characters DROP COLUMN IF EXISTS discord;
