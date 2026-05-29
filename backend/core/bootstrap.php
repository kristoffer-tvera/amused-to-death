<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../api/secrets.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Input.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Services.php';

function backend_db(): Database
{
    static $database = null;
    if ($database === null) {
        $database = new Database($GLOBALS['dbservername'], $GLOBALS['dbusername'], $GLOBALS['dbpassword'], $GLOBALS['dbname'], $GLOBALS['dbtable_log']);
    }

    return $database;
}

function backend_tables(): array
{
    return [
        'characters' => $GLOBALS['dbtable_characters'],
        'raids' => $GLOBALS['dbtable_raids'],
        'attendance' => $GLOBALS['dbtable_attendance'],
        'auth' => $GLOBALS['dbtable_auth'],
        'log' => $GLOBALS['dbtable_log'],
        'app' => $GLOBALS['dbtable_app'],
    ];
}
