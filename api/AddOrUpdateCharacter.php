<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }

    $discord = $_SESSION['auth'];

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'secrets.php';

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

    $returnPath = $_POST["return"];
    $returnPath = htmlspecialchars($returnPath);

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    if(!empty($id)){
        if(isset($_SESSION['admin'])){
            if(isset($_POST["discord"]) && !empty($_POST["discord"])){
                $discord = htmlspecialchars($_POST["discord"]);
            } else {
                $discord = 'error#1234';
            }
    
            $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET name=?, class=?, main=?, realm=?, role_tank=?, role_heal=?, role_dps=?, discord=? WHERE id=?");
            $stmt->bind_param('siisiiisi', $name, $class, $main, $realm, $tank, $heal, $dps, $discord, $id);
    
            $sql = "UPDATE $dbtable_characters SET name=$name, class=$class, main=$main, realm=$realm, role_tank=$tank, role_heal=$heal, role_dps=$dps, discord=$discord, WHERE id=$id";
        } else {
            $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET name=?, class=?, main=?, realm=?, role_tank=?, role_heal=?, role_dps=? WHERE id=?");
            $stmt->bind_param('siisiiii', $name, $class, $main, $realm, $tank, $heal, $dps, $id);
    
            $sql = "UPDATE $dbtable_characters SET name=$name, class=$class, main=$main, realm=$realm, role_tank=$tank, role_heal=$heal, role_dps=$dps WHERE id=$id";
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_characters` (name, class, main, realm, role_tank, role_heal, role_dps, discord) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('siisiiis', $name, $class, $main, $realm, $tank, $heal, $dps, $discord);

        $sql = "INSERT INTO $dbtable_characters (name, class, main, realm, role_tank, role_heal, role_dps, discord) VALUES ($name, $class, $main, $realm, $tank, $heal, $dps, $discord)";
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