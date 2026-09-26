-- Adds sessions.owner_id: the Blizzard account identifier (OAuth "sub") of the
-- logged-in account. This is the durable ownership key — it matches
-- characters.owner_id, so "my characters" can be resolved from the session
-- without relying on the display BattleTag (which can change).

ALTER TABLE sessions ADD COLUMN IF NOT EXISTS owner_id varchar(64);
