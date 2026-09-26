-- Drops characters.raider. Guild membership is now implied by ownership
-- (owner_id) — every owned character belongs to a guild member — so the manual
-- raider flag is redundant. Features that used it (add-all-raiders, roster purge)
-- now key on owner_id instead.

ALTER TABLE characters DROP COLUMN IF EXISTS raider;
