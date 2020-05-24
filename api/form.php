<?php
session_start();
if(isset($_SESSION['alreadyposted'])){
    header("Location: /thank-you?error=true");
    exit;
} else {
    $_SESSION["alreadyposted"] = true;
}

class Field {
     public $name;
     public $value;

     function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
      }
}

$fields = [];

$file = 'apps.txt';

//=======================================================================================================
// Create new webhook in your Discord channel settings and copy&paste URL
//=======================================================================================================

$webhookurl = "https://discordapp.com/api/webhooks/466060619976671245/XiB_pHAsX22tQKqoYis81-U_KPnffP9Y0vk_1UCve3U_GQUwefwnUTa-5JzHDdjwCD5M";

//=======================================================================================================
// Compose message. You can use Markdown
// Message Formatting -- https://discordapp.com/developers/docs/reference#message-formatting
//========================================================================================================

$embeds = [];
$embeds[] = array('fields' => $fields);

// $json_data = array ('content'=>"$msg", 'embeds'=>$embeds);
$json_data = array ('content'=>"New application!", 'embeds'=>$embeds);
$make_json = json_encode($json_data);

$json = json_encode([
    /*
     * The general "message" shown above your embeds
     */
    "content" => "New Application!",
    /*
     * The username shown in the message
     */
    //"username" => "MyUsername",
    /*
     * The image location for the senders image
     */
    //"avatar_url" => "https://pbs.twimg.com/profile_images/972154872261853184/RnOg6UyU_400x400.jpg",
    /*
     * Whether or not to read the message in Text-to-speech
     */
    //"tts" => false,
    /*
     * File contents to send to upload a file
     */
    // "file" => "",
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

            // The URL of where your title will be a link to
            // "url" => "https://www.google.com/",

            /* A timestamp to be displayed below the embed, IE for when an an article was posted
             * This must be formatted as ISO8601
             */
            // "timestamp" => "2018-03-10T19:15:45-05:00",

            // The integer color to be used on the left side of the embed
            "color" => hexdec( "FFFFFF" ),

            // Footer object
            // "footer" => [
            //     "text" => "Google TM",
            //     "icon_url" => "https://pbs.twimg.com/profile_images/972154872261853184/RnOg6UyU_400x400.jpg"
            // ],

            // Image object
            // "image" => [
            //     "url" => "https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png"
            // ],

            // Thumbnail object
            // "thumbnail" => [
            //     "url" => "https://pbs.twimg.com/profile_images/972154872261853184/RnOg6UyU_400x400.jpg"
            // ],

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
header("Location: /thank-you");
exit;

?>