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

    $class = $_POST["class"];
    $class = htmlspecialchars(strip_tags($class));

    $main = $_POST["main"];
    $main = htmlspecialchars(strip_tags($main));
    if($main == -1){
        $main = NULL;
    }

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    // if(!empty($id) && !empty($main)){
    //     $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET change_date=now(), name=?, class=?, main=? WHERE id=?");
    //     $stmt->bind_param('siii', $name, $class, $main, $id);
    // } elseif(!empty($id) && empty($main)){
    //     $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET change_date=now(), name=?, class=? WHERE id=?");
    //     $stmt->bind_param('sii', $name, $class, $id);
    // }
    // elseif(!empty($main)){
    //     $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class, main) VALUES (?, ?, ?)");
    //     $stmt->bind_param('sii', $name, $class, $main);
    // } else {
    //     $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class) VALUES (?, ?)");
    //     $stmt->bind_param('si', $name, $class);
    // }

    if(!empty($id)){
        $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET change_date=now(), name=?, class=?, main=? WHERE id=?");
        $stmt->bind_param('siii', $name, $class, $main, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class, main) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $name, $class, $main);
    }

    echo json_encode($stmt->execute());
?>