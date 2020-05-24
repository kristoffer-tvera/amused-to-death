<?php

function GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $sql = "SELECT * FROM `$dbtable_characters`";

    $result = $conn-> query($sql);

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function ClassFromId($id) {
    switch ($id) {
        case 0:
            return "Druid";
        case 1:
            return "Paladin";
        case 2:
            return "Warrior";
        case 3:
            return "Demon Hunter";
        case 4:
            return "Hunter";
        case 5:
            return "Mage";
        case 6:
            return "Rogue";
        case 7:
            return "Death Knight";
        case 8:
            return "Priest";
        case 9:
            return "Warlock";
        case 10:
            return "Shaman";
        case 11:
            return "Monk";
    }
    return "Invalid id";
}

?>
