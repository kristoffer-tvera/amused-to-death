<?php session_start();
if(isset($_SESSION['alreadyposted'])){
    header("Location: /thank-you/?error=true");
    exit;
} else {
    $_SESSION["alreadyposted"] = true;
}

include_once 'secrets.php';

$file = 'apps.txt';

//=======================================================================================================
// Compose message. You can use Markdown
// Message Formatting -- https://discordapp.com/developers/docs/reference#message-formatting
//========================================================================================================

$json = json_encode([
    /*
     * The general "message" shown above your embeds
     */
    "content" => "New Application!",
    /*
     * An array of Embeds
     */
    "embeds" => [
        /*
         * Our first embed
         */
        [
            // Set the title for your embed
            "title" => "Application",

            // The type of your embed, will ALWAYS be "rich"
            "type" => "rich",

            // A description for your embed
            "description" => "",

            // The integer color to be used on the left side of the embed
            "color" => hexdec( "FFFFFF" ),

            // Author object
            "author" => [
                "name" => "amusedtodeath.eu",
                "url" => "https://amusedtodeath.eu"
            ],

            // Field array of objects
            "fields" => [
                [
                    "name" => "Name+Btag",
                    "value" => htmlspecialchars($_POST["name"]),
                    "inline" => false
                ],
                [
                    "name" => "Class",
                    "value" => htmlspecialchars($_POST["class"]),
                    "inline" => false
                ],
                [
                    "name" => "Spec",
                    "value" => htmlspecialchars($_POST["spec"]),
                    "inline" => false
                ],
                [
                    "name" => "Armory link",
                    "value" => htmlspecialchars($_POST["armory"]),
                    "inline" => false
                ],
                [
                    "name" => "Logs link",
                    "value" => htmlspecialchars($_POST["logs"]),
                    "inline" => false
                ],
                [
                    "name" => "UI Screenshot",
                    "value" => htmlspecialchars($_POST["ui"]),
                    "inline" => false
                ],
                [
                    "name" => "Addons + addonexperience",
                    "value" => htmlspecialchars($_POST["addons"]),
                    "inline" => false
                ],
                [
                    "name" => "Why did you want to join",
                    "value" => htmlspecialchars($_POST["why"]),
                    "inline" => false
                ],
                [
                    "name" => "Last guild",
                    "value" => htmlspecialchars($_POST["lastguild"]),
                    "inline" => false
                ],
                [
                    "name" => "Raiding History",
                    "value" => htmlspecialchars($_POST["history"]),
                    "inline" => false
                ],
                [
                    "name" => "Alts or other specs",
                    "value" => htmlspecialchars($_POST["alts"]),
                    "inline" => false
                ]
                
            ]
        ]
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

file_put_contents($file, $json, FILE_APPEND | LOCK_EX);

$ch = curl_init( $webhookurl );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );
//If you need to debug, or find out why you can't send message uncomment line below, and execute script.
//echo $response;

header("Location: /thank-you/");
exit;

?>