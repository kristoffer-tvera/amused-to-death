<?php session_start();
    $token = '';
    if(isset($_GET["token"]) && !empty($_GET["token"])){
        $token = strtolower(htmlspecialchars($_GET["token"]));
    }

    if(empty($token)) die('No token provided');

    include_once 'db.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_auth` WHERE token=?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $auth = $result->fetch_assoc();

    if(empty($auth)) die("Invalid or expired token");

    $_SESSION['auth'] = $auth['discord'];

    header('Location: /boosting/');
    exit;
?>