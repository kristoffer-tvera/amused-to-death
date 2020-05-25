<?php
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

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    if(!empty($id)){
        $stmt = $conn->prepare("UPDATE `$dbtable_raids` SET change_date=now(), name=?, gold=? WHERE id=?");
        $stmt->bind_param('sii', $name, $gold, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_raids` (name, gold) VALUES (?, ?)");
        $stmt->bind_param('si', $name, $gold);
    }

    echo json_encode($stmt->execute());
?>