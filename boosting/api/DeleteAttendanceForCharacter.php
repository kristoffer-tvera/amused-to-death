<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
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
        $returnPath = "/boosting/";
    }

    include_once 'db.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("DELETE FROM `$dbtable_attendance` WHERE characterId=? AND raidId=?");
    $stmt->bind_param('ii', $characterId, $raidId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    header('Location: ' . $returnPath . '?id=' . $returnId);
    exit;
?>