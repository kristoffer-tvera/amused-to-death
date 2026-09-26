-- Adds sessions.is_admin: the admin flag decided at login time from the user's
-- guild rank, then read cheaply on every request (no per-request roster lookup).
--
-- Replaces the old static App:Admins username allowlist. Admin status now flows
-- from in-game guild rank (see App:BattleNet:AdminMaxRank).

ALTER TABLE sessions ADD COLUMN IF NOT EXISTS is_admin boolean NOT NULL DEFAULT false;
