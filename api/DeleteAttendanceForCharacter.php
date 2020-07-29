<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $characterId = htmlspecialchars($_GET["characterId"]);
    $raidId = htmlspecialchars($_GET["raidId"]);
    $returnPath = htmlspecialchars($_GET["return"]);
    $returnId = htmlspecialchars($_GET["returnId"]);
    if(empty($returnPath)){
        $returnPath = "/";
    }

    include_once 'secrets.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("DELETE FROM `$dbtable_attendance` WHERE characterId=? AND raidId=?");
    $stmt->bind_param('ii', $characterId, $raidId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Query Logging
    $sql = "DELETE FROM $dbtable_attendance WHERE characterId=$characterId AND raidId=$raidId";
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();
    
    header('Location: ' . $returnPath . '?id=' . $returnId);
    exit;
?>