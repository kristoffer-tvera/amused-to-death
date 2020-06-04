<?php
require 'db.php';

function Initialize_database($dbservername, $dbusername, $dbpassword, $dbname){
    $conn = new mysqli($dbservername, $dbusername, $dbpassword);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . $dbname;
    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        echo "Error creating database: " . $conn->error;
        return false;
    }
}

function Initialize_tables($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $dbtable_raids, $dbtable_attendance){    
    // Create connection
    $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    
    $charactersSql = "CREATE TABLE $dbtable_characters (
        id INT(6) AUTO_INCREMENT PRIMARY KEY, 
        ilvl INT(3) NOT NULL DEFAULT '0',
        main INT(6), 
        FOREIGN KEY (main) REFERENCES $dbtable_characters(id),
        name VARCHAR(30) NOT NULL,
        class INT(2) NOT NULL,
        realm VARCHAR(50) NOT NULL DEFAULT 'draenor',
        role_tank BIT NOT NULL DEFAULT 0,
        role_heal BIT NOT NULL DEFAULT 0,
        role_dps BIT NOT NULL DEFAULT 0,
        hidden BIT NOT NULL DEFAULT 0,
        added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";
    
    if ($conn->query($charactersSql) === TRUE) {
        echo "\n$dbtable_characters successfully created";
    } else {
        echo "\n$dbtable_characters was NOT created";
        return false;
    }

    $raidSql = "CREATE TABLE $dbtable_raids (
        id INT(6) AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(50),
        gold INT(8) NOT NULL DEFAULT 0,
        added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";
        
    if ($conn->query($raidSql) === TRUE) {
        echo "\n$dbtable_raids successfully created";
    } else {
        echo "\n$dbtable_raids was NOT created";
        return false;
    }

    $attendanceSql = "CREATE TABLE $dbtable_attendance (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        characterId INT(6) NOT NULL,
        FOREIGN KEY (characterId) REFERENCES $dbtable_characters(id) ON DELETE CASCADE,
        raidId INT(6) NOT NULL,
        FOREIGN KEY (raidId) REFERENCES $dbtable_raids(id) ON DELETE CASCADE,
        bosses INT(2) NOT NULL,
        paid TINYINT(1) NOT NULL DEFAULT '0',
        added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";
        
    if ($conn->query($attendanceSql) === TRUE) {
        echo "\n$dbtable_raids successfully created";
    } else {
        echo "\n$dbtable_raids was NOT created";
        return false;
    }

    return true;
}

if(Initialize_database($dbservername, $dbusername, $dbpassword, $dbname)){
    echo "\nDB created successfully";
} else {
    echo "\nfailure in creating db";
}

if(Initialize_tables($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $dbtable_raids, $dbtable_attendance)){
    echo "\nTables created successfully";
} else {
    echo "\nfailure in creating tables";
}

?>