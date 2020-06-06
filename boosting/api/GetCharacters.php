<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
        exit;
    }

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'db.php';

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "SELECT * FROM `$dbtable_characters`";
    
    $result = $conn->query($sql);
    
    echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
?>