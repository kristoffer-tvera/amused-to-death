<?php 
session_start();

if(!isset($_SESSION['auth']) && isset($_COOKIE['auth']) && !empty($_COOKIE['auth'])){

    $auth_invalid = false;

    $auth = $_COOKIE['auth'];

    include_once './api/db.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_auth` WHERE token=?");
    $stmt->bind_param('s', $auth);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $auth = $result->fetch_assoc();

    if(empty($auth)) $auth_invalid = true;

    if(!$auth_invalid && strtotime($auth['expire_date']) < strtotime("now")) $auth_invalid = true;

    if(!$auth_invalid){
        $discord_username = $auth['discord'];
        $_SESSION['auth'] = $discord_username;
        if(in_array($discord_username, $admins)){
            $_SESSION['admin'] = true;
        }
    } else {
        setcookie('auth', null, -1, '/');
    }
    
    
}


?>