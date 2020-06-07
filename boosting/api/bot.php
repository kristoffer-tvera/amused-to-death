<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $token = '';
    if(isset($_POST["token"]) && !empty($_POST["token"])){
        $token = htmlspecialchars($_POST["token"]);
    }
    if(empty($token)) die('No token provided');

    $discord = '';
    if(isset($_POST["discord"]) && !empty($_POST["discord"])){
        $discord = htmlspecialchars($_POST["discord"]);
    }
    if(empty($discord)) die('No token provided');

    $expire = date_create('+10 minute')->format('Y-m-d H:i:s');

    echo $token;
    echo $discord;
    echo $expire;

    include_once 'db.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("INSERT INTO `$dbtable_auth` (token, discord, expire_date) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $token, $discord, $expire);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo true;
?>