<?php  session_start();

    // header("Access-Control-Allow-Origin: *");
    // header("Content-Type: application/json; charset=UTF-8");
    // header("Access-Control-Allow-Methods: GET");
    // header("Access-Control-Max-Age: 3600");
    // header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    include_once 'db.php';

    //=======================================================================================================
    // Blizzard oAuth endpoint URL
    //=======================================================================================================
    $url = "https://eu.battle.net/oauth/token";

    $return = '';
    if(isset($_GET["return"]) && !empty($_GET["return"])){
        $return = htmlspecialchars($_GET["return"]);
    } else {
        $return = "/boosting/";
    }

    $ch = curl_init( $url );

    $params = ['grant_type'=>'client_credentials'];
    curl_setopt( $ch, CURLOPT_POST, true);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt( $ch, CURLOPT_USERPWD, "$bnetusername:$bnetpassword");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);

    $response = curl_exec( $ch );
    $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE);

    curl_close( $ch );

    if ($status !== 200) {
        throw new Exception('Failed to create client_credentials access token.');
    }

    $decoded = json_decode($response);

    $_SESSION['token'] = $decoded->access_token;
    $_SESSION['token_create'] = time();
    $_SESSION['token_expire'] = $decoded->expires_in;

    header('Location: ' . $return);
    // exit;
?>