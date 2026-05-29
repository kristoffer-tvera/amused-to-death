<?php

/**
 * me.php - JSON API endpoint for the React frontend.
 * Returns session/user info and data queries as JSON.
 * Keeps all existing PHP logic intact, just provides a JSON interface.
 */

include_once '../partials/_session_start.php';
include_once 'secrets.php';
include_once 'helper.php';

header('Content-Type: application/json; charset=UTF-8');

// ─── Auth status ─────────────────────────────────────────────────────────────
if (
    !isset($_GET['characters']) && !isset($_GET['all_characters']) && !isset($_GET['character']) &&
    !isset($_GET['alts_for']) && !isset($_GET['raids']) && !isset($_GET['raid']) &&
    !isset($_GET['attendance_for_raid']) && !isset($_GET['attendance_for_character']) &&
    !isset($_GET['apps']) && !isset($_GET['app']) && !isset($_GET['log']) &&
    !isset($_GET['bnet_status']) && !isset($_GET['destroy_session'])
) {
    // Default: return auth info
    if (isset($_SESSION['auth'])) {
        echo json_encode([
            'user' => $_SESSION['auth'],
            'admin' => isset($_SESSION['admin']) && $_SESSION['admin'] === true,
        ]);
    } else {
        echo json_encode(null);
    }
    exit;
}

// ─── Destroy session ─────────────────────────────────────────────────────────
if (isset($_GET['destroy_session'])) {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// ─── Protected endpoints below require auth ──────────────────────────────────
if (empty($_SESSION['auth'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ─── My characters ───────────────────────────────────────────────────────────
if (isset($_GET['characters'])) {
    $data = GetMyCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $_SESSION['auth']);
    echo json_encode($data);
    exit;
}

// ─── All characters ──────────────────────────────────────────────────────────
if (isset($_GET['all_characters'])) {
    $data = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
    echo json_encode($data);
    exit;
}

// ─── Single character ────────────────────────────────────────────────────────
if (isset($_GET['character'])) {
    $id = intval($_GET['character']);
    $data = GetCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $id);
    echo json_encode($data);
    exit;
}

// ─── Alts for character ──────────────────────────────────────────────────────
if (isset($_GET['alts_for'])) {
    $id = intval($_GET['alts_for']);
    $data = GetAltsForCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $id);
    echo json_encode($data);
    exit;
}

// ─── Raids ───────────────────────────────────────────────────────────────────
if (isset($_GET['raids'])) {
    $data = GetRaids($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids);
    echo json_encode($data);
    exit;
}

// ─── Single raid ─────────────────────────────────────────────────────────────
if (isset($_GET['raid'])) {
    $id = intval($_GET['raid']);
    $data = GetRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $id);
    echo json_encode($data);
    exit;
}

// ─── Attendance for raid ─────────────────────────────────────────────────────
if (isset($_GET['attendance_for_raid'])) {
    $id = intval($_GET['attendance_for_raid']);
    $data = GetAttendanceForRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_characters, $id);
    echo json_encode($data);
    exit;
}

// ─── Attendance for character ────────────────────────────────────────────────
if (isset($_GET['attendance_for_character'])) {
    $id = intval($_GET['attendance_for_character']);
    $data = GetAttendanceForCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_raids, $id);
    echo json_encode($data);
    exit;
}

// ─── Applications (admin or auth) ───────────────────────────────────────────
if (isset($_GET['apps'])) {
    $data = GetApps($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_app);
    echo json_encode($data);
    exit;
}

// ─── Single application ─────────────────────────────────────────────────────
if (isset($_GET['app'])) {
    $id = intval($_GET['app']);
    $data = GetApp($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_app, $id);
    echo json_encode($data);
    exit;
}

// ─── Activity log (admin only) ───────────────────────────────────────────────
if (isset($_GET['log'])) {
    if (empty($_SESSION['admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    $data = GetLog($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_log);
    echo json_encode($data);
    exit;
}

// ─── Battle.net token status ─────────────────────────────────────────────────
if (isset($_GET['bnet_status'])) {
    $remaining = 0;
    if (isset($_SESSION['token_expire'])) {
        $remaining = $_SESSION['token_expire'] - time();
        if ($remaining < 0) $remaining = 0;
    }
    echo json_encode([
        'has_token' => !empty($_SESSION['token']),
        'remaining' => $remaining,
    ]);
    exit;
}
