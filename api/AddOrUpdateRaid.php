<?php session_start();

    if(empty($_SESSION['admin'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }
    
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'secrets.php';
    include_once 'helper.php';

    $id = $_POST["id"];
    $id = htmlspecialchars(strip_tags($id));

    $name = $_POST["name"];
    $name = htmlspecialchars(strip_tags($name));

    $gold = $_POST["gold"];
    $gold = htmlspecialchars(strip_tags($gold));

    $paid = 0;
    if(isset($_POST["paid"]) && !empty($_POST["paid"])){
        $paid = htmlspecialchars($_POST["paid"]);
    }

    $comment = $_POST["comment"];
    $comment = htmlspecialchars(strip_tags($comment));

    $returnPath = $_POST["return"];
    $returnPath = htmlspecialchars($returnPath);

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    if(!empty($id)){
        $stmt = $conn->prepare("UPDATE `$dbtable_raids` SET name=?, gold=?, paid=?, comment=? WHERE id=?");
        $stmt->bind_param('siisi', $name, $gold, $paid, $comment, $id);

        $sql = "UPDATE $dbtable_raids SET name=$name, gold=$gold, paid=$paid, comment=(removed) WHERE id=$id";
    } else {
        $stmt = $conn->prepare("INSERT INTO `$dbtable_raids` (name, gold, paid, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('siis', $name, $gold, $paid, $comment);

        $sql = "INSERT INTO $dbtable_raids (name, gold, paid, comment) VALUES ($name, $gold, $paid, $comment)";
    }

    $stmt->execute();

    // Query Logging
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();

    if(empty($id)){
        $id = $stmt->insert_id;
        AnnouncementNewRaid($name, "https://amusedtodeath.eu/raid/?id=" . $id, $webhookurl_announcement);
    }

    header('Location: ' . $returnPath . '?id=' . $id);
    exit;
?>