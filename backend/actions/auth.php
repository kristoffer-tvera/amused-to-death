<?php
require_once __DIR__ . '/../core/bootstrap.php';

$adminDiscordUsernames = backend_admins();

if (query_value('discord') !== '') {
    redirect_to('https://discord.com/api/oauth2/authorize?client_id=' . $discord_client_id . '&redirect_uri=' . $discord_oauth_redir . '&response_type=code&scope=identify');
}

if (query_value('code') !== '') {
    $code = query_value('code');
    $curl = curl_init('https://discord.com/api/oauth2/token');
    $headers = ['Accept: application/json'];
    $params = [
        'grant_type' => 'authorization_code',
        'client_id' => $discord_client_id,
        'client_secret' => $discord_client_secret,
        'redirect_uri' => $discord_oauth_redir,
        'code' => $code,
    ];

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($status !== 200) {
        fail(401, 'Discord OAuth failed');
    }

    $decoded = json_decode($response);
    $accessToken = $decoded->access_token ?? '';
    $refreshToken = $decoded->refresh_token ?? '';

    $headers[] = 'Authorization: Bearer ' . $accessToken;
    curl_setopt($curl, CURLOPT_URL, 'https://discord.com/api/users/@me');
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    curl_close($curl);

    $user = json_decode($response);
    $discordUsername = $user->username ?? '';
    if ($discordUsername === '') {
        fail(401, 'Discord username missing');
    }

    $tables = backend_tables();
    $character = backend_db()->fetchOne("SELECT * FROM `{$tables['characters']}` WHERE discord=?", 's', $discordUsername);
    if (empty($character)) {
        http_response_code(401);
        die('You have no characters. Have an officer make one for you.');
    }

    $_SESSION['auth'] = $discordUsername;
    if (in_array($discordUsername, $adminDiscordUsernames, true)) {
        $_SESSION['admin'] = true;
    }

    setcookie('discord_auth', $refreshToken, time() + 60 * 60 * 24 * 365, '/');
    redirect_to('/');
}

$token = query_value('token');
if ($token === '') {
    http_response_code(400);
    die('No token provided');
}

$tables = backend_tables();
$auth = backend_db()->fetchOne("SELECT * FROM `{$tables['auth']}` WHERE token=?", 's', $token);
if (empty($auth)) {
    http_response_code(401);
    die('Invalid token');
}

if (strtotime($auth['expire_date']) < strtotime('now')) {
    http_response_code(401);
    die('Expired token');
}

$discordUsername = $auth['discord'];
$_SESSION['auth'] = $discordUsername;
if (in_array($discordUsername, $adminDiscordUsernames, true)) {
    $_SESSION['admin'] = true;
}

setcookie('auth', $token, time() + 60 * 60 * 24 * 365, '/');
redirect_to('/');
