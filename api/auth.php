<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
    include_once 'secrets.php';

    if(isset($_GET['discord']) && !empty($_GET['discord'])){
        header('Location: https://discord.com/api/oauth2/authorize?client_id=' . $discord_client_id . '&redirect_uri=' . $discord_oauth_redir . '&response_type=code&scope=identify');
        die();
    }

    if(isset($_GET['code']) && !empty($_GET['code'])){
        $code = htmlspecialchars($_GET['code']);

        $curl = curl_init( 'https://discord.com/api/oauth2/token' );
        $headers[] = 'Accept: application/json';
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => $discord_client_id,
            'client_secret' => $discord_client_secret,
            'redirect_uri' => $discord_oauth_redir,
            'code' => $code
        ];

        curl_setopt( $curl, CURLOPT_POST, true);
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec( $curl );
        $status = curl_getinfo( $curl, CURLINFO_HTTP_CODE);
        $decoded = json_decode($response);

        $accessToken = $decoded->access_token;
        $refreshToken = $decoded->refresh_token;
        $headers[] = 'Authorization: Bearer ' . $accessToken;
        curl_setopt ($curl, CURLOPT_URL, 'https://discord.com/api/users/@me');
        curl_setopt( $curl, CURLOPT_POST, false);
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec( $curl );
        $user = json_decode($response);

        curl_close( $curl );

        $discord_username = $user->username . '#' . $user->discriminator;

        $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            die('Connection failed: ' . $conn->connect_error);
        }
        $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` WHERE discord=?");
        $stmt->bind_param('s', $discord_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $characters = $result->fetch_assoc();

        if(empty($characters)) die('You have no characters. Have an officer make one for you.');

        $_SESSION['auth'] = $discord_username;
        if(in_array($discord_username, $admins)){
            $_SESSION['admin'] = true;
        }

        setcookie('discord_auth', $refreshToken, time() + 60*60*24*365, '/');
        header('Location: /');
        die();
    }


    $token = '';
    if(isset($_GET['token']) && !empty($_GET['token'])){
        $token = htmlspecialchars($_GET['token']);
    }

    if(empty($token)) die('No token provided');

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    $stmt = $conn->prepare("SELECT * FROM `$dbtable_auth` WHERE token=?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $auth = $result->fetch_assoc();

    if(empty($auth)) die('Invalid token');

    if(strtotime($auth['expire_date']) < strtotime('now')) die('Expired token');

    $discord_username = $auth['discord'];
    $_SESSION['auth'] = $discord_username;
    if(in_array($discord_username, $admins)){
        $_SESSION['admin'] = true;
    }

    setcookie('auth', $token, time() + 60*60*24*365, '/');

    header('Location: /');
    die();
?>