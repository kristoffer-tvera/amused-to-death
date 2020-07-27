<?php session_start();

$return = $_POST["return"];
$return = htmlspecialchars($return);


if(isset($_SESSION['apply'])){
    header("Location: " . $return);
    exit;
} else {
    $_SESSION["apply"] = true;
}

include_once 'secrets.php';

$fields = [];
foreach($_POST as $key => $value){
    if($key == "return") continue;

    $fields[] = [
        "name" => $key,
        "value" => htmlspecialchars($value),
        "inline" => false
    ];
}

$json = json_encode([
    "content" => "New Application!",
    "embeds" => [
        [
            "title" => "Application",
            "type" => "rich",
            "description" => "",
            "color" => hexdec( "FFFFFF" ),
            "author" => [
                "name" => "amusedtodeath.eu",
                "url" => "https://amusedtodeath.eu"
            ],
            "fields" => $fields,
        ]
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

$file = 'apps.txt';
file_put_contents($file, $json, FILE_APPEND | LOCK_EX);

$ch = curl_init( $webhookurl_recruitment );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );
//If you need to debug, or find out why you can't send message uncomment line below, and execute script.
//echo $response;

header("Location: " . $return);
exit;
?>