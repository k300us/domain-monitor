<?php
declare(strict_types=1);

// Copy to config/config.php and adjust values.

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'cuocsong675a_monitor',
        'user' => 'cuocsong675a_monitor',
        'pass' => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'telegram' => [
        // Create a bot with @BotFather and set token/chat_id if you want alerts.
        'enabled' => false,
        'bot_token' => 'PUT_TOKEN_HERE',
        'chat_id' => 'PUT_CHAT_ID_HERE',
        'api_base' => 'https://api.telegram.org',
    ],
    'app' => [
        'timezone' => 'Asia/Ho_Chi_Minh',
        'debug' => true,
    ],
];

