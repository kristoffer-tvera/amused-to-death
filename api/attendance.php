<?php session_start();
    include_once 'secrets.php';
    include_once "helper.php";

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $postData = file_get_contents('php://input');
    $post = json_decode($postData, TRUE); 

    if($post["action"] == 0) {
        echo var_dump($_POST);
       AddAttendance($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $post["raidId"], $post["charId"]);
       
        $raids = GetRaids($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids);
        $jsonResult = json_encode($raids);
        echo $jsonResult;
    }


?>