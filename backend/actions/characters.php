<?php
require_once __DIR__ . '/../core/bootstrap.php';

cors_json_headers('GET, POST');
$action = query_value('action', post_value('action'));

switch ($action) {
    case 'save':
        require_auth();
        $id = character_save([
            'id' => post_value('id'),
            'name' => post_value('name'),
            'realm' => post_value('realm'),
            'class' => post_value('class'),
            'main' => post_value('main', -1),
            'role_tank' => post_value('role_tank', 0),
            'role_heal' => post_value('role_heal', 0),
            'role_dps' => post_value('role_dps', 0),
            'raider' => post_value('raider', 0),
            'vip' => post_value('vip', 0),
            'discord' => post_value('discord'),
        ]);
        $return = post_value('return');
        if ($return !== '') {
            redirect_with_id($return, $id);
        }
        json_response(['id' => $id]);

    case 'hide':
        character_hide(int_value(query_value('id')));
        $return = query_value('return');
        if ($return !== '') {
            redirect_to($return);
        }
        json_response(['success' => true]);

    default:
        fail(404, 'Unknown character action');
}
