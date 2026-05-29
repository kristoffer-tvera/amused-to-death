<?php

// Copy this file to backend/secrets.php and fill in real values.
// Never commit backend/secrets.php.

// Database connection
$dbservername = 'localhost';
$dbusername = 'database_user';
$dbpassword = 'database_password';
$dbname = 'amused_to_death';

// Database table names
$dbtable_characters = 'characters';
$dbtable_raids = 'raids';
$dbtable_attendance = 'attendance';
$dbtable_auth = 'auth';
$dbtable_log = 'log';
$dbtable_app = 'app';

// Discord OAuth
$discord_client_id = 'discord_client_id';
$discord_client_secret = 'discord_client_secret';
$discord_oauth_redir = 'https://amusedtodeath.eu/backend/actions/auth.php';

// Discord usernames that should get admin access.
// Values should match Discord's username returned by /users/@me.
$admins = [
    'discord_username',
];

// Discord webhooks
$webhookurl_announcement = 'https://discord.com/api/webhooks/...';
$webhookurl_recruitment = 'https://discord.com/api/webhooks/...';

// Battle.net client credentials
$bnetusername = 'battle_net_client_id';
$bnetpassword = 'battle_net_client_secret';

// Bot/API token endpoint key (sent as X-API-KEY)
$x_api_key = 'change_me_to_a_long_random_value';
