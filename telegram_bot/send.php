<?php
declare(strict_types=1);

[$config, $db] = require __DIR__ . '/../src/bootstrap.php';

$text = $argv[1] ?? '';
if (trim($text) === '') {
    fwrite(STDERR, "Usage: php telegram_bot/send.php \"message\"\n");
    exit(1);
}

$telegram = new TelegramService($config['telegram'] ?? []);
$ok = $telegram->sendMessage($text);

echo $ok ? "OK\n" : "FAILED (check telegram config)\n";

