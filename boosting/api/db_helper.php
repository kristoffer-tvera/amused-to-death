<?php

function GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters`");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function GetCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $id) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` where id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function GetRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $id) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_raids` where id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function GetRaids($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_raids`");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function GetAttendanceForRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_characters, $id){
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_attendance` INNER JOIN `$dbtable_characters` ON `$dbtable_attendance`.`characterId`=`$dbtable_characters`.`id` WHERE `$dbtable_attendance`.`raidId`=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function GetAttendanceForCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_raids, $id){
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_attendance` INNER JOIN `$dbtable_raids` ON `$dbtable_attendance`.`raidId`=`$dbtable_raids`.`id` WHERE `characterId`=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
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
