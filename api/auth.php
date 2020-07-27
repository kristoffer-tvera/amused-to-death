<?php session_start();
    $token = '';
    if(isset($_GET["token"]) && !empty($_GET["token"])){
        $token = htmlspecialchars($_GET["token"]);
    }

    if(empty($token)) die('No token provided');

    include_once 'secrets.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_auth` WHERE token=?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $auth = $result->fetch_assoc();

    if(empty($auth)) die("Invalid token");

    if(strtotime($auth['expire_date']) < strtotime("now")) die("Expired token");

    $discord_username = $auth['discord'];
    $_SESSION['auth'] = $discord_username;
    if(in_array($discord_username, $admins)){
        $_SESSION['admin'] = true;
    }

    setcookie('auth', $token, time() + (86400 * 14), '/');

    header('Location: /');
    exit;
?>