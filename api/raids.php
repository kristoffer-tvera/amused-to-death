<?php
        include_once "secrets.php";
        include_once "helper.php";

      header("Access-Control-Allow-Origin: *");
      header("Content-Type: application/json; charset=UTF-8");
      header("Access-Control-Allow-Methods: POST");
      header("Access-Control-Max-Age: 3600");
      header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        header('Content-Type: application/json');
        $raids = GetRaids($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids);
        $jsonResult = json_encode($raids);
        echo $jsonResult;
?>