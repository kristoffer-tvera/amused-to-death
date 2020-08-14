<?php 
        include_once "../secrets.php";
        include_once "../helper.php";

        $allowedOrigins = array(
                'http://localhost:8080'
              );

              if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
                foreach ($allowedOrigins as $allowedOrigin) {
                  if (preg_match('#' . $allowedOrigin . '#', $_SERVER['HTTP_ORIGIN'])) {
                    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
                    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                    header('Access-Control-Max-Age: 1000');
                    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
                    break;
                  }
                }
              }


        $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
        $jsonResult = json_encode($characters);
        header('Content-Type: application/json');
        echo $jsonResult;
        // // echo $characters;
        // var_dump($jsonResult);
?>