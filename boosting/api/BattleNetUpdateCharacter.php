<?php session_start();

    if(empty($_SESSION['auth'])){
        http_response_code(401);
        require './unauthorized.php';
        exit;
    }

    include_once 'db.php';
    
    $return = '';
    if(isset($_GET["return"]) && !empty($_GET["return"])){
        $return = htmlspecialchars($_GET["return"]);
    } else {
        $return = "/boosting/";
    }

    $id = '';
    if(isset($_GET["id"]) && !empty($_GET["id"])){
        $id = strtolower(htmlspecialchars($_GET["id"]));
    }

    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $char = $result->fetch_assoc();

    $name = urlencode(strtolower($char["name"]));
    $realm = strtolower($char["realm"]);
    //=======================================================================================================
    // Blizzard oAuth endpoint URL
    //=======================================================================================================

    $url = "https://eu.api.blizzard.com/profile/wow/character/" . $realm . "/" . $name . "?namespace=profile-eu&locale=en_GB&access_token=" ;

    $ch = curl_init( $url . $_SESSION['token']);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec( $ch );
    $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE);

    curl_close( $ch );

    if ($status !== 200) {
        // throw new Exception('Failed to get character profile.');
        header('Location: ' . $return . '&error=true');
    }

    $decoded = json_decode($response);


    $ilvl = $decoded->average_item_level;

    $stmt = $conn->prepare("UPDATE `$dbtable_characters` SET change_date=now(), ilvl=? WHERE id=?");
    $stmt->bind_param('ii', $ilvl, $id);
    $stmt->execute();

    // Query Logging
    $request = 'BNET => ' . $url . 'SECRET';
    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
    $log->bind_param('ss', $request, $_SESSION['auth']);
    $log->execute();
    

    header('Location: ' . $return);
    // exit;
?>