<?php

include_once "secrets.php";
include_once "helper.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$userId = $_GET["userId"];

if(!empty($userId)) {
    header('Content-Type: application/json');
    $characters = GetUserCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $userId);
    $jsonResult = json_encode($characters);
    echo $jsonResult;
    exit;
}

?>