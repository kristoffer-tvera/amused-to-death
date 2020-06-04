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

    $realm = $_POST["realm"];
    $realm = htmlspecialchars(strip_tags($realm));

    $class = $_POST["class"];
    $class = htmlspecialchars(strip_tags($class));

    $main = $_POST["main"];
    $main = htmlspecialchars(strip_tags($main));

    $tank = 0;
    if(isset($_POST["role_tank"]) && !empty($_POST["role_tank"])){
        $tank = htmlspecialchars($_POST["role_tank"]);
    }

    $heal = 0;
    if(isset($_POST["role_heal"]) && !empty($_POST["role_heal"])){
        $heal = htmlspecialchars($_POST["role_heal"]);
    }

    $dps = 0;
    if(isset($_POST["role_dps"]) && !empty($_POST["role_dps"])){
        $dps = htmlspecialchars($_POST["role_dps"]);
    }

    if($main == -1){
        $main = NULL;
    }

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    if(!empty($id)){
        $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET change_date=now(), name=?, class=?, main=?, realm=?, role_tank=?, role_heal=?, role_dps=? WHERE id=?");
        $stmt->bind_param('siisiiii', $name, $class, $main, $realm, $tank, $heal, $dps, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class, main, realm, role_tank, role_heal, role_dps) VALUES (?, ?, ?)");
        $stmt->bind_param('siisiii', $name, $class, $main, $realm, $tank, $heal, $dps);
    }

    echo json_encode($stmt->execute());
?>