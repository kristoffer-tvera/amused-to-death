<?php
require_once __DIR__ . '/../core/bootstrap.php';

cors_json_headers('POST');
$return = post_value('return', '/apply');

if (post_value('pepe') !== 'meme') {
    redirect_to($return);
}

$result = application_save([
    'id' => post_value('id'),
    'auth' => post_value('auth'),
    'name' => post_value('name'),
    'server' => post_value('server'),
    'btag' => post_value('btag'),
    'spec' => post_value('spec'),
    'ui' => post_value('ui'),
    'reason' => post_value('reason'),
    'history' => post_value('history'),
    'alts' => post_value('alts'),
]);

$authorUrl = '/app/' . $result['id'] . '?auth=' . $result['auth'];
$_SESSION['apply'] = 'https://amusedtodeath.eu' . $authorUrl;
redirect_to($authorUrl);
