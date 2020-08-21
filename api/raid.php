<?php
        include_once "secrets.php";
        include_once "helper.php";

      header("Access-Control-Allow-Origin: *");
      header("Content-Type: application/json; charset=UTF-8");
      header("Access-Control-Allow-Methods: POST");
      header("Access-Control-Max-Age: 3600");
      header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

            $id = $_GET["id"];

            if(!empty($id)){
              header('Content-Type: application/json');
              $raid = GetRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $id);
              $jsonResult = json_encode($raid);
              echo $jsonResult;
              exit;
            }

            $id = $_POST["id"];
            $name = $_POST["name"];
            $name = htmlspecialchars(strip_tags($name));
            $gold = $_POST["gold"];
            $gold = htmlspecialchars(strip_tags($gold));
            $start_date = $_POST["start_date"];
            $start_date = htmlspecialchars(strip_tags($start_date));

            if(!empty($id)) {
                  $id = htmlspecialchars(strip_tags($id));

                  UpdateRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $name, $gold, $id);
                  echo "raid updated";
                  exit;

            }
            else {
                  CreateRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $name, $gold, $start_date);
                  echo "raid created";
                  exit;
            }
            echo "Error";
?>