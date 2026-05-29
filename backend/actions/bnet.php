<?php
require_once __DIR__ . '/../core/bootstrap.php';

require_auth();
$action = query_value('action');

if ($action === 'token') {
    $ch = curl_init('https://eu.battle.net/oauth/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['grant_type' => 'client_credentials']);
    curl_setopt($ch, CURLOPT_USERPWD, "$bnetusername:$bnetpassword");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        fail(502, 'Failed to create Battle.net access token');
    }

    $decoded = json_decode($response);
    $_SESSION['token'] = $decoded->access_token;
    $_SESSION['token_create'] = time();
    $_SESSION['token_expire'] = time() + intval($decoded->expires_in);
    redirect_to(query_value('return', '/'));
}

if ($action === 'update_character') {
    $id = int_value(query_value('id'));
    $ajax = query_value('ajax') !== '';
    $return = query_value('return', '/');
    $character = character_get($id);

    if (empty($character)) {
        fail(404, 'Character not found');
    }

    if (empty($_SESSION['token'])) {
        fail(401, 'Missing Battle.net token');
    }

    $name = urlencode(strtolower($character['name']));
    $realm = strtolower($character['realm']);
    $url = "https://eu.api.blizzard.com/profile/wow/character/{$realm}/{$name}?namespace=profile-eu&locale=en_GB&access_token=";

    $ch = curl_init($url . $_SESSION['token']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        if ($ajax) {
            http_response_code($status);
            echo $name;
            exit;
        }
        redirect_to($return . '&error=true');
    }

    $decoded = json_decode($response);
    $ilvl = intval($decoded->average_item_level ?? 0);
    $tables = backend_tables();
    backend_db()->execute("UPDATE `{$tables['characters']}` SET ilvl=? WHERE id=?", 'ii', $ilvl, $id);

    if (!$ajax) {
        backend_db()->log('BNET => ' . $url . 'SECRET');
        redirect_to($return);
    }

    http_response_code(200);
    echo $name;
    exit;
}

fail(404, 'Unknown Battle.net action');
