-- Decode legacy htmlspecialchars() output back into plain text.
--
-- Background: the old PHP app ran every free-text field through
-- htmlspecialchars(strip_tags($value)) before storing it, and the .NET port
-- kept that behavior for byte-compatibility. We have now dropped input-time
-- encoding (React escapes on render; Dapper parameterizes every query), so
-- historical rows would otherwise render as literal entity soup
-- (e.g. "Tom &amp; Jerry", "it&#039;s") next to new raw rows.
--
-- This reverses ONLY the htmlspecialchars encoding. It maps:
--     &lt;   -> <
--     &gt;   -> >
--     &quot; -> "
--     &#039; -> '
--     &amp;  -> &   (done LAST, so a literally-typed "&lt;" that was stored as
--                    "&amp;lt;" decodes to "&lt;" and not to "<")
--
-- It CANNOT recover data removed by strip_tags(): any text that looked like an
-- HTML tag (e.g. "dps > 5000" collapsing after the ">") was destroyed at write
-- time and is unrecoverable. This migration only fixes the reversible half.
--
-- Idempotency note: re-running would further collapse any real "&amp;" that a
-- user legitimately typed post-migration. DbUp records applied scripts and never
-- re-runs them, so this executes exactly once. Do not run it manually twice.

-- Shared decode expression, applied per column. Order matters: amp last.
-- applications ---------------------------------------------------------------
UPDATE applications SET
    name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    server = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(server,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    battle_tag = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(battle_tag,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    spec = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(spec,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    ui_screenshot_url = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(ui_screenshot_url,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    reason = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(reason,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    history = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(history,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    alts = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(alts,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&')
WHERE
    name LIKE '%&%' OR server LIKE '%&%' OR battle_tag LIKE '%&%' OR spec LIKE '%&%'
    OR ui_screenshot_url LIKE '%&%' OR reason LIKE '%&%' OR history LIKE '%&%'
    OR alts LIKE '%&%';

-- raids ----------------------------------------------------------------------
UPDATE raids SET
    name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&'),
    comment = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(comment,
        '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''), '&amp;', '&')
WHERE
    name LIKE '%&%' OR comment LIKE '%&%';
