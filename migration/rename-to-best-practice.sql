-- One-off rename script: brings the LIVE Neon database in line with the
-- best-practice names now baked into backend/Migrations/0001_BaseSchema.sql.
--
-- Run this ONCE in the Neon SQL editor. It is a pure set of RENAMEs, so all
-- existing data is preserved. After running it, the app (updated to match) will
-- work against the renamed schema, and DbUp's idempotent 0001 will be a no-op.
--
-- This is NOT a DbUp migration and is intentionally not embedded in the API.
-- It exists only to migrate the one pre-existing database that predates the
-- clean base schema.
--
-- Safe to run inside a single transaction: if anything fails, nothing changes.

BEGIN;

-- attendance: camelCase FK columns -> snake_case (removes all quoting grief)
ALTER TABLE attendance RENAME COLUMN "characterId" TO character_id;
ALTER TABLE attendance RENAME COLUMN "raidId"      TO raid_id;

-- Timestamps: added_date/change_date -> created_at/updated_at (all tables)
ALTER TABLE characters   RENAME COLUMN added_date  TO created_at;
ALTER TABLE characters   RENAME COLUMN change_date TO updated_at;
ALTER TABLE raids        RENAME COLUMN added_date  TO created_at;
ALTER TABLE raids        RENAME COLUMN change_date TO updated_at;
ALTER TABLE applications RENAME COLUMN added_date  TO created_at;
ALTER TABLE applications RENAME COLUMN change_date TO updated_at;
ALTER TABLE attendance   RENAME COLUMN added_date  TO created_at;
ALTER TABLE attendance   RENAME COLUMN change_date TO updated_at;

-- applications: clarify overloaded / cryptic names
ALTER TABLE applications RENAME COLUMN auth TO edit_token;
ALTER TABLE applications RENAME COLUMN btag TO battle_tag;
ALTER TABLE applications RENAME COLUMN ui   TO ui_screenshot_url;

-- auth table -> sessions (it stores login sessions); provider-neutral columns
ALTER TABLE auth RENAME TO sessions;
ALTER TABLE sessions RENAME COLUMN discord     TO username;
ALTER TABLE sessions RENAME COLUMN expire_date TO expires_at;

-- Rename the change-tracking function + triggers to match the new column name.
ALTER FUNCTION set_change_date() RENAME TO set_updated_at;
ALTER TRIGGER trg_characters_change_date  ON characters   RENAME TO trg_characters_updated_at;
ALTER TRIGGER trg_raids_change_date       ON raids        RENAME TO trg_raids_updated_at;
ALTER TRIGGER trg_app_change_date         ON applications RENAME TO trg_applications_updated_at;
ALTER TRIGGER trg_attendance_change_date  ON attendance   RENAME TO trg_attendance_updated_at;

-- The trigger function body still references NEW.change_date; redefine it to
-- use the renamed column. (Renaming the function does not rewrite its body.)
CREATE OR REPLACE FUNCTION set_updated_at() RETURNS trigger AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

COMMIT;
