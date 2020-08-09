<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
        exit;
    }

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'secrets.php';

    $characterId = $_POST["characterId"];
    $characterId = htmlspecialchars(strip_tags($characterId));

    $raidId = $_POST["raidId"];
    $raidId = htmlspecialchars(strip_tags($raidId));

    $bosses = $_POST["bosses"];
    $bosses = htmlspecialchars(strip_tags($bosses));

    $paid = $_POST["paid"];
    $paid = htmlspecialchars(strip_tags($paid));

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("UPDATE `$dbtable_attendance` SET bosses=?, paid=? WHERE characterId=? AND raidID=?");
    $stmt->bind_param('iiii', $bosses, $paid, $characterId, $raidId);

    echo json_encode($stmt->execute());

    // Query Logging
    $sql = "UPDATE $dbtable_attendance SET bosses=$bosses, paid=$paid WHERE characterId=$characterId AND raidID=$raidId";
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $sql, $_SESSION['auth']);
    $log->execute();
?>