<?php
require 'secrets.php';

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

function Initialize_tables($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $dbtable_raids, $dbtable_attendance, $dbtable_auth, $dbtable_log){   
    $error = false; 
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
        discord VARCHAR(50),
        added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
    
    if ($conn->query($charactersSql) === TRUE) {
        echo "\n$dbtable_characters successfully created";
    } else {
        echo "\n$dbtable_characters was NOT created";
        $error = true; 
    }

    $raidSql = "CREATE TABLE $dbtable_raids (
        id INT(6) AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(50),
        gold INT(8) NOT NULL DEFAULT 0,
        added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        start_date DATETIME NULL DEFAULT NULL
        )";
        
    if ($conn->query($raidSql) === TRUE) {
        echo "\n$dbtable_raids successfully created";
    } else {
        echo "\n$dbtable_raids was NOT created";
        $error = true; 
    }

    $authSql = "CREATE TABLE $dbtable_auth (
        id INT(6) AUTO_INCREMENT PRIMARY KEY, 
        token VARCHAR(26),
        discord VARCHAR(50),
        expire_date DATETIME NOT NULL
        )";
        
    if ($conn->query($authSql) === TRUE) {
        echo "\n$dbtable_auth successfully created";
    } else {
        echo "\n$dbtable_auth was NOT created";
        $error = true; 
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
        change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (raidId, characterId)
        )";
        
    if ($conn->query($attendanceSql) === TRUE) {
        echo "\n$dbtable_raids successfully created";
    } else {
        echo "\n$dbtable_raids was NOT created";
        $error = true; 
    }

    $logSql = "CREATE TABLE $dbtable_log (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        `query` TEXT NOT NULL,
        `user` VARCHAR(40) NOT NULL,
        `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )";

    if ($conn->query($logSql) === TRUE) {
        echo "\n$dbtable_log successfully created";
    } else {
        echo "\n$dbtable_log was NOT created";
        $error = true; 
    }

    if($error){
        echo "\nOne or more tables has NOT been created";
    }

    return !$error;
}

if(Initialize_database($dbservername, $dbusername, $dbpassword, $dbname)){
    echo "\nDB created successfully";
} else {
    echo "\nfailure in creating db";
}

if(Initialize_tables($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $dbtable_raids, $dbtable_attendance, $dbtable_auth, $dbtable_log)){
    echo "\nTables created successfully";
} else {
    echo "\nfailure in creating tables";
}

?>