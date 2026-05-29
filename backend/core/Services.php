<?php

function character_list(bool $onlyMine = false): array
{
    $tables = backend_tables();
    if ($onlyMine) {
        return backend_db()->fetchAll("SELECT * FROM `{$tables['characters']}` WHERE discord=? AND hidden=0", 's', current_user());
    }

    return backend_db()->fetchAll("SELECT * FROM `{$tables['characters']}` WHERE hidden=0");
}

function character_get(int $id): ?array
{
    $tables = backend_tables();
    return backend_db()->fetchOne("SELECT * FROM `{$tables['characters']}` WHERE id=?", 'i', $id);
}

function character_alts(int $id): array
{
    $tables = backend_tables();
    return backend_db()->fetchAll("SELECT * FROM `{$tables['characters']}` WHERE main=? AND hidden=0", 'i', $id);
}

function character_save(array $data): int
{
    require_auth();
    $tables = backend_tables();
    $db = backend_db();

    $id = int_value($data['id'] ?? 0);
    $name = (string)($data['name'] ?? '');
    $class = int_value($data['class'] ?? 0);
    $main = nullable_main($data['main'] ?? -1);
    $realm = (string)($data['realm'] ?? '');
    $tank = boolish($data['role_tank'] ?? 0);
    $heal = boolish($data['role_heal'] ?? 0);
    $dps = boolish($data['role_dps'] ?? 0);
    $raider = boolish($data['raider'] ?? 0);
    $vip = boolish($data['vip'] ?? 0);
    $discord = is_admin() ? (string)($data['discord'] ?? current_user()) : current_user();

    if ($id > 0) {
        if (is_admin()) {
            $db->execute("UPDATE `{$tables['characters']}` SET name=?, class=?, main=?, realm=?, role_tank=?, role_heal=?, role_dps=?, raider=?, vip=?, discord=? WHERE id=?", 'siisiiiiisi', $name, $class, $main, $realm, $tank, $heal, $dps, $raider, $vip, $discord, $id);
            $db->log("UPDATE {$tables['characters']} SET name={$name}, class={$class}, main={$main}, realm={$realm}, role_tank={$tank}, role_heal={$heal}, role_dps={$dps}, raider={$raider}, vip={$vip}, discord={$discord} WHERE id={$id}");
        } else {
            $db->execute("UPDATE `{$tables['characters']}` SET name=?, class=?, main=?, realm=?, role_tank=?, role_heal=?, role_dps=?, raider=?, vip=? WHERE id=?", 'siisiiiiii', $name, $class, $main, $realm, $tank, $heal, $dps, $raider, $vip, $id);
            $db->log("UPDATE {$tables['characters']} SET name={$name}, class={$class}, main={$main}, realm={$realm}, role_tank={$tank}, role_heal={$heal}, role_dps={$dps}, raider={$raider}, vip={$vip} WHERE id={$id}");
        }
        return $id;
    }

    $db->execute("INSERT INTO `{$tables['characters']}` (name, class, main, realm, role_tank, role_heal, role_dps, raider, vip, discord) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 'siisiiiiis', $name, $class, $main, $realm, $tank, $heal, $dps, $raider, $vip, $discord);
    $id = $db->insertId();
    $db->log("INSERT INTO {$tables['characters']} (name, class, main, realm, role_tank, role_heal, role_dps, raider, vip, discord) VALUES ({$name}, {$class}, {$main}, {$realm}, {$tank}, {$heal}, {$dps}, {$raider}, {$vip}, {$discord})");
    return $id;
}

function character_hide(int $id): void
{
    require_admin();
    $tables = backend_tables();
    backend_db()->execute("UPDATE `{$tables['characters']}` SET hidden=1 WHERE id=?", 'i', $id);
    backend_db()->log("UPDATE {$tables['characters']} SET hidden=1 WHERE id={$id}");
}

function raid_list(): array
{
    $tables = backend_tables();
    return backend_db()->fetchAll("SELECT * FROM `{$tables['raids']}`");
}

function raid_get(int $id): ?array
{
    $tables = backend_tables();
    return backend_db()->fetchOne("SELECT * FROM `{$tables['raids']}` WHERE id=?", 'i', $id);
}

function raid_save(array $data): int
{
    require_admin();
    $tables = backend_tables();
    $db = backend_db();

    $id = int_value($data['id'] ?? 0);
    $name = (string)($data['name'] ?? '');
    $gold = int_value($data['gold'] ?? 0);
    $paid = boolish($data['paid'] ?? 0);
    $comment = (string)($data['comment'] ?? '');

    if ($id > 0) {
        $db->execute("UPDATE `{$tables['raids']}` SET name=?, gold=?, paid=?, comment=? WHERE id=?", 'siisi', $name, $gold, $paid, $comment, $id);
        $db->log("UPDATE {$tables['raids']} SET name={$name}, gold={$gold}, paid={$paid}, comment=(removed) WHERE id={$id}");
        return $id;
    }

    $db->execute("INSERT INTO `{$tables['raids']}` (name, gold, paid, comment) VALUES (?, ?, ?, ?)", 'siis', $name, $gold, $paid, $comment);
    $id = $db->insertId();
    $db->log("INSERT INTO {$tables['raids']} (name, gold, paid, comment) VALUES ({$name}, {$gold}, {$paid}, (removed))");
    discord_webhook($GLOBALS['webhookurl_announcement'] ?? '', '@here New raid (' . $name . ') posted! Visit https://amusedtodeath.eu/raid/?id=' . $id . ' to sign up!', ['everyone']);
    return $id;
}

function attendance_for_raid(int $raidId): array
{
    $tables = backend_tables();
    return backend_db()->fetchAll("SELECT a.id, a.added_date, a.bosses, a.paid, a.raidId, a.characterId, c.name, c.class, c.main, c.discord, c.vip, c.ilvl, c.role_tank, c.role_heal, c.role_dps FROM `{$tables['attendance']}` AS a INNER JOIN `{$tables['characters']}` AS c ON a.characterId=c.id WHERE a.raidId=? ORDER BY a.added_date ASC", 'i', $raidId);
}

function attendance_for_character(int $characterId): array
{
    $tables = backend_tables();
    return backend_db()->fetchAll("SELECT * FROM `{$tables['attendance']}` INNER JOIN `{$tables['raids']}` ON `{$tables['attendance']}`.raidId=`{$tables['raids']}`.id WHERE characterId=?", 'i', $characterId);
}

function attendance_add(int $characterId, int $raidId, int $bosses): void
{
    require_auth();
    $tables = backend_tables();
    backend_db()->execute("INSERT INTO `{$tables['attendance']}` (characterId, raidId, bosses) VALUES (?, ?, ?)", 'iii', $characterId, $raidId, $bosses);
    backend_db()->log("INSERT INTO {$tables['attendance']} (characterId, raidId, bosses) VALUES({$characterId}, {$raidId}, {$bosses})");
}

function attendance_update(int $characterId, int $raidId, int $bosses, int $paid): bool
{
    require_auth();
    $tables = backend_tables();
    $stmt = backend_db()->execute("UPDATE `{$tables['attendance']}` SET bosses=?, paid=? WHERE characterId=? AND raidId=?", 'iiii', $bosses, $paid, $characterId, $raidId);
    backend_db()->log("UPDATE {$tables['attendance']} SET bosses={$bosses}, paid={$paid} WHERE characterId={$characterId} AND raidId={$raidId}");
    return $stmt->affected_rows >= 0;
}

function attendance_delete(int $characterId, int $raidId): void
{
    require_auth();
    $tables = backend_tables();
    backend_db()->execute("DELETE FROM `{$tables['attendance']}` WHERE characterId=? AND raidId=?", 'ii', $characterId, $raidId);
    backend_db()->log("DELETE FROM {$tables['attendance']} WHERE characterId={$characterId} AND raidId={$raidId}");
}

function attendance_add_all_raiders(int $raidId): void
{
    require_admin();
    $tables = backend_tables();
    backend_db()->execute("INSERT IGNORE INTO `{$tables['attendance']}` (characterId, raidId, bosses) SELECT `{$tables['characters']}`.id, ?, 0 FROM `{$tables['characters']}` WHERE `{$tables['characters']}`.raider=1", 'i', $raidId);
    backend_db()->log("INSERT IGNORE INTO {$tables['attendance']} (characterId, raidId, bosses) SELECT {$tables['characters']}.id, {$raidId}, 0 FROM {$tables['characters']} WHERE {$tables['characters']}.raider=1");
}

function attendance_remove_zero_bosses(int $raidId): void
{
    require_admin();
    $tables = backend_tables();
    backend_db()->execute("DELETE FROM `{$tables['attendance']}` WHERE bosses=0 AND raidId=?", 'i', $raidId);
    backend_db()->log("DELETE FROM {$tables['attendance']} WHERE bosses=0 AND raidId={$raidId}");
}

function attendance_set_all_paid(int $raidId): void
{
    require_admin();
    $tables = backend_tables();
    backend_db()->execute("UPDATE `{$tables['attendance']}` SET paid=1 WHERE raidId=?", 'i', $raidId);
    backend_db()->log("UPDATE {$tables['attendance']} SET paid=1 WHERE raidId={$raidId}");
}

function application_list(): array
{
    $tables = backend_tables();
    return backend_db()->fetchAll("SELECT id, name, server, spec, change_date FROM `{$tables['app']}`");
}

function application_get(int $id): ?array
{
    $tables = backend_tables();
    return backend_db()->fetchOne("SELECT * FROM `{$tables['app']}` WHERE id=?", 'i', $id);
}

function application_save(array $data): array
{
    $tables = backend_tables();
    $db = backend_db();
    $id = int_value($data['id'] ?? 0);
    $auth = (string)($data['auth'] ?? '');
    $name = (string)($data['name'] ?? '');
    $server = (string)($data['server'] ?? '');
    $btag = (string)($data['btag'] ?? '');
    $spec = (string)($data['spec'] ?? '');
    $ui = (string)($data['ui'] ?? '');
    $reason = (string)($data['reason'] ?? '');
    $history = (string)($data['history'] ?? '');
    $alts = (string)($data['alts'] ?? '');

    if ($id > 0) {
        $db->execute("UPDATE `{$tables['app']}` SET name=?, server=?, btag=?, spec=?, ui=?, reason=?, history=?, alts=? WHERE id=? AND auth=?", 'ssssssssis', $name, $server, $btag, $spec, $ui, $reason, $history, $alts, $id, $auth);
        $db->log("UPDATE {$tables['app']} SET (...) WHERE id={$id} AND auth=SECRET");
    } else {
        $auth = bin2hex(random_bytes(9));
        $db->execute("INSERT INTO `{$tables['app']}` (name, auth, server, btag, spec, ui, reason, history, alts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 'sssssssss', $name, $auth, $server, $btag, $spec, $ui, $reason, $history, $alts);
        $id = $db->insertId();
        $db->log("INSERT INTO {$tables['app']} (...) VALUES (...)");
    }

    $title = ($id > 0 ? 'App update!' : 'New app!') . ' (' . $name . ' - ' . $server . ') -- https://amusedtodeath.eu/app/?id=' . $id;
    discord_webhook($GLOBALS['webhookurl_recruitment'] ?? '', $title);

    return ['id' => $id, 'auth' => $auth];
}

function auth_token_create(string $token, string $discord): void
{
    require_api_key();
    $tables = backend_tables();
    $expire = date_create('+365 day')->format('Y-m-d H:i:s');
    backend_db()->execute("INSERT INTO `{$tables['auth']}` (token, discord, expire_date) VALUES (?, ?, ?)", 'sss', $token, $discord, $expire);
}

function log_list(): array
{
    require_admin();
    $tables = backend_tables();
    return backend_db()->fetchAll("SELECT * FROM `{$tables['log']}`");
}

function discord_webhook(string $webhook, string $content, array $mentions = []): void
{
    if ($webhook === '') {
        return;
    }

    $payload = ['content' => $content];
    if (!empty($mentions)) {
        $payload['allowed_mentions'] = ['parse' => $mentions];
    }

    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);
    curl_close($ch);
}
