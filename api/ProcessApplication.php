<?php session_start();

$return = $_POST["return"];
$return = htmlspecialchars($return);

$pepe = $_POST["pepe"];
if($pepe != "meme") {
    header("Location: " . $return);
    exit;
}

include_once 'secrets.php';

$id = $_POST["id"];
$id = htmlspecialchars(strip_tags($id));

$auth  = $_POST["auth"];
$auth = htmlspecialchars(strip_tags($auth));

$name = $_POST["name"];
$name = htmlspecialchars(strip_tags($name));

$server = $_POST["server"];
$server = htmlspecialchars(strip_tags($server));

$btag = $_POST["btag"];
$btag = htmlspecialchars(strip_tags($btag));

$spec = $_POST["spec"];
$spec = htmlspecialchars(strip_tags($spec));

$ui = $_POST["ui"];
$ui = htmlspecialchars(strip_tags($ui));

$reason = $_POST["reason"];
$reason = htmlspecialchars(strip_tags($reason));

$history = $_POST["history"];
$history = htmlspecialchars(strip_tags($history));

$alts = $_POST["alts"];
$alts = htmlspecialchars(strip_tags($alts));

$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(!empty($id)){
    if(isset($_SESSION['admin'])){
        $stmt = $conn->prepare("UPDATE `$dbtable_app` SET name=?, server=?, btag=?, spec=?, ui=?, reason=?, history=?, alts=? WHERE id=? AND auth=?");
        $stmt->bind_param('ssssssssis', $name, $server, $btag, $spec, $ui, $reason, $history, $alts, $id, $auth);

        $sql = "UPDATE $dbtable_app SET (...) WHERE id=$id AND auth=SECRET";
    } else {
        $stmt = $conn->prepare("UPDATE `$dbtable_app` SET name=?, server=?, btag=?, spec=?, ui=?, reason=?, history=?, alts=? WHERE id=? AND auth=?");
        $stmt->bind_param('ssssssssis', $name, $server, $btag, $spec, $ui, $reason, $history, $alts, $id, $auth);

        $sql = "UPDATE $dbtable_app SET (...) WHERE id=$id AND auth=SECRET";
    }
} else {
    $auth = bin2hex(random_bytes(9));
    $stmt = $conn->prepare("INSERT INTO `$dbtable_app` (name, auth, server, btag, spec, ui, reason, history, alts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param('sssssssss', $name, $auth, $server, $btag, $spec, $ui, $reason, $history, $alts);

    $sql = "INSERT INTO $dbtable_app (...) VALUES (...)";
}

$stmt->execute();

$user = "Public";
if(isset($_SESSION['auth'])){
    $user = $_SESSION['auth'];
}

// Query Logging
$log = $conn->prepare("INSERT INTO `$dbtable_log` (query, user) VALUES (?, ?)");
$log->bind_param('ss', $sql, $user);
$log->execute();

$discordWebhookTitle = "App update! (";
if(empty($id)){
    $discordWebhookTitle = "New app! (";
    $id = $stmt->insert_id;
}

$discordWebhookTitle = $discordWebhookTitle . $name . " - " . $server . ") -- https://amusedtodeath.eu/app/?id=" . $id;

$json = json_encode([
    "content" => $discordWebhookTitle
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

$ch = curl_init( $webhookurl_recruitment );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );

$author_url = "https://amusedtodeath.eu/app/?id=" . $id . "&auth=" . $auth;

$_SESSION["apply"] = $author_url;
header("Location: " . $author_url);
exit;
?>
