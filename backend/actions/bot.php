<?php
require_once __DIR__ . '/../core/bootstrap.php';

cors_json_headers('POST');

$token = post_value('token');
$discord = post_value('discord');

if ($token === '') {
    fail(400, 'No token provided');
}

if ($discord === '') {
    fail(400, 'No discord provided');
}

auth_token_create($token, $discord);
json_response(true);
