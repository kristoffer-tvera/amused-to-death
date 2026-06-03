<?php
require_once __DIR__ . '/../core/bootstrap.php';

function initialize_database(string $host, string $username, string $password, string $database): bool
{
    try {
        $connection = new mysqli($host, $username, $password);
    } catch (mysqli_sql_exception $exception) {
        die("Connection to database host '{$host}' failed: " . $exception->getMessage() . PHP_EOL);
    }

    return $connection->query('CREATE DATABASE IF NOT EXISTS `' . $connection->real_escape_string($database) . '`') === true;
}

function initialize_tables(mysqli $connection, array $tables): bool
{
    $queries = [
        "CREATE TABLE IF NOT EXISTS `{$tables['characters']}` (
            id INT(6) AUTO_INCREMENT PRIMARY KEY,
            ilvl INT(3) NOT NULL DEFAULT '0',
            main INT(6),
            FOREIGN KEY (main) REFERENCES `{$tables['characters']}`(id),
            name VARCHAR(30) NOT NULL,
            class INT(2) NOT NULL,
            realm VARCHAR(50) NOT NULL DEFAULT 'stormscale',
            role_tank BIT NOT NULL DEFAULT 0,
            role_heal BIT NOT NULL DEFAULT 0,
            role_dps BIT NOT NULL DEFAULT 0,
            hidden BIT NOT NULL DEFAULT 0,
            raider BIT NOT NULL DEFAULT 0,
            vip BIT NOT NULL DEFAULT 0,
            discord VARCHAR(50),
            added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS `{$tables['raids']}` (
            id INT(6) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50),
            gold INT(8) NOT NULL DEFAULT 0,
            paid BIT NOT NULL DEFAULT 0,
            comment VARCHAR(2000),
            added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS `{$tables['app']}` (
            id INT(6) AUTO_INCREMENT PRIMARY KEY,
            auth VARCHAR(18) NOT NULL,
            name VARCHAR(50),
            server VARCHAR(50),
            btag VARCHAR(250),
            spec VARCHAR(250),
            ui VARCHAR(250),
            reason TEXT,
            history TEXT,
            alts TEXT,
            added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS `{$tables['auth']}` (
            id INT(6) AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(26),
            discord VARCHAR(50),
            expire_date DATETIME NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS `{$tables['attendance']}` (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            characterId INT(6) NOT NULL,
            FOREIGN KEY (characterId) REFERENCES `{$tables['characters']}`(id) ON DELETE CASCADE,
            raidId INT(6) NOT NULL,
            FOREIGN KEY (raidId) REFERENCES `{$tables['raids']}`(id) ON DELETE CASCADE,
            bosses INT(2) NOT NULL,
            paid TINYINT(1) NOT NULL DEFAULT '0',
            added_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            change_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY (raidId, characterId)
        )",
        "CREATE TABLE IF NOT EXISTS `{$tables['log']}` (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `query` TEXT NOT NULL,
            `user` VARCHAR(40) NOT NULL,
            `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )",
    ];

    $ok = true;
    foreach ($queries as $query) {
        if ($connection->query($query) !== true) {
            $ok = false;
            echo "Failed query: " . $connection->error . PHP_EOL;
        }
    }

    return $ok;
}

if (!initialize_database($dbservername, $dbusername, $dbpassword, $dbname)) {
    die('Failed creating database' . PHP_EOL);
}

echo 'Database ready' . PHP_EOL;

$connection = null;
try {
    $connection = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
} catch (mysqli_sql_exception $exception) {
    die("Connection to database '{$dbname}' on host '{$dbservername}' failed: " . $exception->getMessage() . PHP_EOL);
}

if (initialize_tables($connection, backend_tables())) {
    echo 'Tables ready' . PHP_EOL;
} else {
    echo 'One or more tables failed' . PHP_EOL;
}
