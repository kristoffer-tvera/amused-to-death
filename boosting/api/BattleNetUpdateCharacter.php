<?php  session_start();

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

    $name = 'bsl';

    //=======================================================================================================
    // Blizzard oAuth endpoint URL
    //=======================================================================================================

    $url = "https://eu.api.blizzard.com/profile/wow/character/draenor/" . $name . "?namespace=profile-eu&locale=en_GB&access_token=" . $_SESSION['token'];

    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec( $ch );
    $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE);

    curl_close( $ch );

    if ($status !== 200) {
        throw new Exception('Failed to get character profile.');
    }

    $decoded = json_decode($response);


    $ilvl = $decoded->average_item_level;


    header('Location: ' . $return);
    // exit;
?>