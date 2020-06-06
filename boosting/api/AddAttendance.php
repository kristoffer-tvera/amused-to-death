<?php session_start();
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $return = $_POST["return"];
    $return = htmlspecialchars($return);

    include_once 'db.php';

    $character = $_POST["character"];
    $character = htmlspecialchars(strip_tags($character));

    $raid = $_POST["raid"];
    $raid = htmlspecialchars(strip_tags($raid));

    $bosses = $_POST["bosses"];
    $bosses = htmlspecialchars(strip_tags($bosses));

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("INSERT INTO `$dbtable_attendance` (characterId, raidId, bosses) VALUES (?, ?, ?)");
    $stmt->bind_param('iii', $character, $raid, $bosses);

    $stmt->execute();

    header('Location: ' . $return . '?id=' . $raid);
    exit;
?>