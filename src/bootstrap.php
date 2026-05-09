<?php
declare(strict_types=1);

$config = require __DIR__ . '/../config/config.php';

date_default_timezone_set((string)($config['app']['timezone'] ?? 'UTC'));

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Repositories/ProviderRepository.php';
require_once __DIR__ . '/Repositories/TldRepository.php';
require_once __DIR__ . '/Repositories/PriceHistoryRepository.php';
require_once __DIR__ . '/Repositories/AlertRuleRepository.php';
require_once __DIR__ . '/Repositories/AlertsLogRepository.php';
require_once __DIR__ . '/Services/TelegramService.php';

$db = new Database($config);

return [$config, $db];

