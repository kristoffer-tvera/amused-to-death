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
        $returnPath = "/";
    }

    include_once 'secrets.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("INSERT INTO `$dbtable_attendance` (characterId, raidId, bosses) SELECT `$dbtable_characters`.id, ?, 0 FROM `$dbtable_characters` WHERE `$dbtable_characters`.raider=1");
    $stmt->bind_param('i', $raidId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Query Logging
    $sql = "INSERT INTO `$dbtable_attendance` (characterId, raidId, bosses) SELECT `$dbtable_characters`.id, $raidId, 0 FROM `$dbtable_characters` WHERE `$dbtable_characters`.raider=1";
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();
    
    header('Location: ' . $returnPath . '?id=' . $raidId);
    exit;
?>