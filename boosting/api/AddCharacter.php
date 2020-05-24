<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'db.php';

    $name = $_POST["name"];
    $name = htmlspecialchars(strip_tags($name));

    $class = $_POST["class"];
    $class = htmlspecialchars(strip_tags($class));

    $main = $_POST["main"];
    $main = htmlspecialchars(strip_tags($main));

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    if(!empty($main)){
        $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class, main) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $name, $class, $main);
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class) VALUES (?, ?)");
        $stmt->bind_param('si', $name, $class);
    }

    echo json_encode($stmt->execute());
?>