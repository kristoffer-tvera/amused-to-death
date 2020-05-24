<?php
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once 'db.php';

    $character = $_POST["character"];
    $character = htmlspecialchars(strip_tags($character));

    $raid = $_POST["raid"];
    $raid = htmlspecialchars(strip_tags($raid));

    $bosses = $_POST["bosses"];
    $bosses = htmlspecialchars(strip_tags($bosses));

    echo json_encode(AddAttendance($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $name, $raid, $bosses));
?>