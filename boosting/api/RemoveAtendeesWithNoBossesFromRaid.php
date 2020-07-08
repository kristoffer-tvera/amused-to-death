<?php session_start();

    if(empty($_SESSION['admin'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $raidId = htmlspecialchars($_GET["raidId"]);
    $returnPath = htmlspecialchars($_GET["return"]);
    
    if(empty($returnPath)){
        $returnPath = "/boosting/";
    }

    include_once 'db.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("DELETE FROM `$dbtable_attendance` WHERE bosses=0 AND raidId=?");
    $stmt->bind_param('i', $raidId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Query Logging
    $sql = "DELETE FROM $dbtable_attendance WHERE bosses=0 AND raidId=$raidId";
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();
    
    header('Location: ' . $returnPath . '?id=' . $raidId);
    exit;
?>