<?php session_start();

if (!isset($_SESSION['auth']) && isset($_COOKIE['discord_auth']) && !empty($_COOKIE['discord_auth'])) {

    $auth_invalid = false;

    $auth = $_COOKIE['discord_auth'];

    include_once './api/secrets.php';

    $curl = curl_init('https://discord.com/api/oauth2/token');
    $headers[] = 'Accept: application/json';
    $params = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $auth,
        'client_id' => $discord_client_id,
        'client_secret' => $discord_client_secret,
        'redirect_uri' => $discord_oauth_redir,
        'scope' => 'identify'
    ];

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($status != 200) {
        exit;
    }

    $decoded = json_decode($response);

    $accessToken = $decoded->access_token;
    $refreshToken = $decoded->refresh_token;
    $headers[] = 'Authorization: Bearer ' . $accessToken;
    curl_setopt($curl, CURLOPT_URL, 'https://discord.com/api/users/@me');
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    $user = json_decode($response);

    curl_close($curl);

    $discord_username = $user->username;

    if (empty($discord_username)) $auth_invalid = true;

    if (!$auth_invalid) {
        $_SESSION['auth'] = $discord_username;
        if (in_array($discord_username, $admins)) {
            $_SESSION['admin'] = true;
        }
        setcookie('discord_auth', $refreshToken, time() + 60 * 60 * 24 * 365, '/');
    } else {
        setcookie('discord_auth', null, -1, '/');
    }
}

if (!isset($_SESSION['auth']) && isset($_COOKIE['auth']) && !empty($_COOKIE['auth'])) {

    $auth_invalid = false;

    $auth = $_COOKIE['auth'];

    include_once './api/secrets.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_auth` WHERE token=?");
    $stmt->bind_param('s', $auth);
    $stmt->execute();
    $result = $stmt->get_result();

    $auth = $result->fetch_assoc();

    if (empty($auth)) $auth_invalid = true;

    if (!$auth_invalid && strtotime($auth['expire_date']) < strtotime("now")) $auth_invalid = true;

    if (!$auth_invalid) {
        $discord_username = $auth['discord'];
        $_SESSION['auth'] = $discord_username;
        if (in_array($discord_username, $admins)) {
            $_SESSION['admin'] = true;
        }
    } else {
        setcookie('auth', null, -1, '/');
    }
}
