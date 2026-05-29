<?php
require_once __DIR__ . '/../core/bootstrap.php';

cors_json_headers('GET, POST');
$action = query_value('action', post_value('action'));

switch ($action) {
    case 'add':
        $raidId = int_value(post_value('raid'));
        attendance_add(int_value(post_value('character')), $raidId, int_value(post_value('bosses')));
        $return = post_value('return');
        if ($return !== '') {
            redirect_with_id($return, $raidId);
        }
        json_response(['success' => true]);

    case 'update':
        json_response(attendance_update(
            int_value(post_value('characterId')),
            int_value(post_value('raidId')),
            int_value(post_value('bosses')),
            int_value(post_value('paid'))
        ));

    case 'delete':
        $raidId = int_value(query_value('raidId'));
        attendance_delete(int_value(query_value('characterId')), $raidId);
        $return = query_value('return');
        if ($return !== '') {
            redirect_with_id($return, $raidId);
        }
        json_response(['success' => true]);

    default:
        fail(404, 'Unknown attendance action');
}
