<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'db.php';

    $name = $_POST["name"];
    $name = htmlspecialchars(strip_tags($name));

    $gold = $_POST["gold"];
    $gold = htmlspecialchars(strip_tags($gold));

    
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    
    $stmt = $conn->prepare("INSERT INTO `$dbtable_raids` (name, class) VALUES (?, ?)");
    $stmt->bind_param('si', $name, $gold);
    
    echo json_encode($stmt->execute());
?>