<?php
require_once __DIR__ . '/../core/bootstrap.php';

cors_json_headers('GET');

$action = query_value('action');

if ($action === 'me') {
    json_response(isset($_SESSION['auth']) ? ['user' => $_SESSION['auth'], 'admin' => is_admin()] : null);
}

if ($action === 'destroy_session') {
    session_destroy();
    json_response(['success' => true]);
}


if ($action === 'app') {
    $id = int_value(query_value('id'));
    $auth = query_value('auth');
    json_response(application_get($id, is_admin() ? null : $auth));
}

require_auth();

switch ($action) {
    case 'my_characters':
        json_response(character_list(true));
    case 'characters':
        json_response(character_list(false));
    case 'character':
        json_response(character_get(int_value(query_value('id'))));
    case 'alts':
        json_response(character_alts(int_value(query_value('id'))));
    case 'raids':
        json_response(raid_list());
    case 'raid':
        json_response(raid_get(int_value(query_value('id'))));
    case 'attendance_for_raid':
        json_response(attendance_for_raid(int_value(query_value('raidId'))));
    case 'attendance_for_character':
        json_response(attendance_for_character(int_value(query_value('characterId'))));
    case 'apps':
        json_response(application_list());
    case 'log':
        json_response(log_list());
    case 'bnet_status':
        $remaining = 0;
        if (isset($_SESSION['token_expire'])) {
            $expires = intval($_SESSION['token_expire']);
            if ($expires > time()) {
                $remaining = $expires - time();
            } elseif (isset($_SESSION['token_create'])) {
                $remaining = max(0, intval($_SESSION['token_create']) + $expires - time());
            }
        }
        json_response(['has_token' => !empty($_SESSION['token']), 'remaining' => $remaining]);
    default:
        fail(404, 'Unknown data action');
}
