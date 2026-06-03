<?php
require_once __DIR__ . '/../core/bootstrap.php';

cors_json_headers('GET, POST');
$action = query_value('action', post_value('action'));

switch ($action) {
    case 'save':
        $id = raid_save([
            'id' => post_value('id'),
            'name' => post_value('name'),
            'gold' => post_value('gold', 0),
            'paid' => post_value('paid', 0),
            'comment' => post_value('comment'),
        ]);
        $return = post_value('return');
        if ($return !== '') {
            redirect_with_id($return, $id);
        }
        json_response(['id' => $id]);

    case 'add_all_raiders':
        $raidId = int_value(query_value('raidId'));
        attendance_add_all_raiders($raidId);
        $return = query_value('return');
        if ($return !== '') {
            redirect_with_id($return, $raidId);
        }
        json_response(['success' => true]);

    case 'remove_zero_bosses':
        $raidId = int_value(query_value('raidId'));
        attendance_remove_zero_bosses($raidId);
        $return = query_value('return');
        if ($return !== '') {
            redirect_with_id($return, $raidId);
        }
        json_response(['success' => true]);

    case 'set_all_paid':
        attendance_set_all_paid(int_value(query_value('raidId')));
        json_response('success');

    default:
        fail(404, 'Unknown raid action');
}
