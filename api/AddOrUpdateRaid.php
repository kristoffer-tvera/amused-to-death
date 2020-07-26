<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }
    
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'db.php';

    $id = $_POST["id"];
    $id = htmlspecialchars(strip_tags($id));

    $name = $_POST["name"];
    $name = htmlspecialchars(strip_tags($name));

    $gold = $_POST["gold"];
    $gold = htmlspecialchars(strip_tags($gold));

    $returnPath = $_POST["return"];
    $returnPath = htmlspecialchars($returnPath);

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    if(!empty($id)){
        $stmt = $conn->prepare("UPDATE `$dbtable_raids` SET name=?, gold=? WHERE id=?");
        $stmt->bind_param('sii', $name, $gold, $id);

        $sql = "UPDATE $dbtable_raids SET name=$name, gold=$gold WHERE id=$id";
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_raids` (name, gold) VALUES (?, ?)");
        $stmt->bind_param('si', $name, $gold);

        $sql = "INSERT INTO $dbtable_raids (name, gold) VALUES ($name, $gold)";
    }

    $stmt->execute();

    // Query Logging
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();

    if(empty($id)){
        $id = $stmt->insert_id;
    }

    header('Location: ' . $returnPath . '?id=' . $id);
    exit;
?>