<?php session_start();
     include_once "secrets.php";
     include_once "helper.php";

   header("Access-Control-Allow-Origin: *");
   header("Content-Type: application/json; charset=UTF-8");
   header("Access-Control-Allow-Methods: GET");
   header("Access-Control-Max-Age: 3600");
   header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
  $user = $_SESSION['auth'];
  
  if(!empty($user)) {
    $user = strval($user);
    header('Content-Type: application/json');
    $user = GetUser($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_users, $user);
    $jsonResult = json_encode($user);
    echo $jsonResult;
    exit;
}

?>