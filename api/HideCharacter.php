<?php session_start();

    if(empty($_SESSION['auth']) || empty($_SESSION['admin'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }

    include_once 'secrets.php';

    $return = '';
    if(isset($_GET["return"]) && !empty($_GET["return"])){
        $return = htmlspecialchars($_GET["return"]);
    } else {
        $return = "/";
    }

    $id = '';
    if(isset($_GET["id"]) && !empty($_GET["id"])){
        $id = strtolower(htmlspecialchars($_GET["id"]));
    }

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET `hidden` = 1 WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $sql = "UPDATE `$dbtable_characters` SET `hidden` = 1 WHERE id=$id";
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();
    
    header('Location: ' . $return);
?>