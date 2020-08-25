<?php

function GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` WHERE id < 35");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function GetMyCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $discord){
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` WHERE discord=?");
    $stmt->bind_param('s', $discord);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function GetCharacterIds($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT `id` FROM `$dbtable_characters`");
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

function GetUser($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_users, $user) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }
    $user = strval($user);
    $stmt = $conn->prepare("SELECT * FROM $dbtable_users WHERE username=?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function GetUserCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $userId) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` where user_id=?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
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

function UpdateRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $name, $gold, $id) {
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

$stmt = $conn->prepare("UPDATE `$dbtable_raids` SET name=?, gold=? WHERE id=?");
$stmt->bind_param('sii', $name, $gold, $id);

// $sql = "UPDATE $dbtable_raids SET name=$name, gold=$gold WHERE id=$id";

$stmt->execute();

//    // Query Logging
//    $log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
//    $log->bind_param('ss', $sql, $_SESSION['auth']);
//    $log->execute();

}

function CreateRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $name, $gold, $start_date) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

    $stmt = $conn->prepare("INSERT INTO `$dbtable_raids` (name, gold, start_date) VALUES (?, ?, ?)");
    $stmt->bind_param('sis', $name, $gold, $start_date);

    $sql = "INSERT INTO $dbtable_raids (name, gold) VALUES ($name, $gold, $start_date)";

    $stmt->execute();

}

function AddAttendance($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $raidId, $charId) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    $stmt = $conn->prepare("INSERT INTO `$dbtable_attendance` (characterId, raidId, bosses, paid, added_date, change_date) VALUES (?, ?, ?, ?, ?, ?)");
    $createDate = date("Y-m-d H:i:s"); 
    $defaultValue = 0;
    $stmt->bind_param('iiiiss', $charId, $raidId, $defaultValue, $defaultValue, $createDate, $createDate);
    
    $stmt->execute();

    $sql = "INSERT INTO $dbtable_attendance (characterId, raidId, bosses, paid, added_date, change_date VALUES ($charId, $raidId, 0, 0, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))";
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

function GetLog($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_log) {
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn-> connect_error) {
        die("Connection failed: ".$conn-> connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_log`");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function GetAttendanceForRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_characters, $id){
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT a.id, a.added_date, a.bosses, a.paid, a.raidId, a.characterId, c.name, c.class, c.main FROM `$dbtable_attendance` AS a INNER JOIN `$dbtable_characters` AS c ON a.`characterId`=c.`id` WHERE a.`raidId`=? ORDER BY a.`added_date` ASC");
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

function GetAltsForCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $id){
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM `$dbtable_characters` WHERE `main`=?");
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

// function AnnouncementNewRaid($raidname, $url, $webhook){

//     $json = json_encode([
//         "content" => "@here New raid (" . $raidname . ") posted! Visit " . $url . " to sign up!",
//         "allowed_mentions" => [
//             "parse" => ["everyone"]
//         ]
//     ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        
//     $ch = curl_init( $webhook );
//     curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
//     curl_setopt( $ch, CURLOPT_POST, 1);
//     curl_setopt( $ch, CURLOPT_POSTFIELDS, $json);
//     curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
//     curl_setopt( $ch, CURLOPT_HEADER, 0);
//     curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    
//     $response = curl_exec( $ch );
//     $success = curl_getinfo( $ch, CURLINFO_HTTP_CODE) === 200;
//     curl_close( $ch );
//     return $succes;
// }

?>
