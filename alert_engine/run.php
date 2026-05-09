<?php
declare(strict_types=1);

[$config, $db] = require __DIR__ . '/../src/bootstrap.php';
$pdo = $db->pdo();

$ruleRepo = new AlertRuleRepository($pdo);
$priceRepo = new PriceHistoryRepository($pdo);
$alertsRepo = new AlertsLogRepository($pdo);
$telegram = new TelegramService($config['telegram'] ?? []);

function nameById(\PDO $pdo, string $table, int $id): ?string
{
    $col = $table === 'providers' ? 'name' : 'tld';
    $stmt = $pdo->prepare("SELECT {$col} FROM {$table} WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $v = $stmt->fetchColumn();
    return $v === false ? null : (string)$v;
}

$rules = $ruleRepo->enabledRules();
if ($rules === []) {
    echo "No enabled alert rules.\n";
    exit(0);
}

$triggered = 0;
foreach ($rules as $rule) {
    $providerId = (int)$rule['provider_id'];
    $tldId = (int)$rule['tld_id'];
    $threshold = (float)$rule['percent_threshold'];

    $lastTwo = $priceRepo->lastN($providerId, $tldId, 2);
    if (count($lastTwo) < 2) {
        continue;
    }

    $new = (float)$lastTwo[0]['price'];
    $old = (float)$lastTwo[1]['price'];
    if ($old <= 0.0) {
        continue;
    }

    $pct = (($new - $old) / $old) * 100.0;
    if (abs($pct) < $threshold) {
        continue;
    }

    $providerName = nameById($pdo, 'providers', $providerId) ?? (string)$providerId;
    $tld = nameById($pdo, 'tlds', $tldId) ?? (string)$tldId;

    $direction = $pct >= 0 ? 'tăng' : 'giảm';
    $msg = "Cảnh báo giá domain {$tld} ({$providerName}): {$direction} " . number_format(abs($pct), 2) . "%\n"
        . "Cũ: " . number_format($old, 0, '.', ',') . " VND\n"
        . "Mới: " . number_format($new, 0, '.', ',') . " VND";

    $sent = $telegram->sendMessage($msg);
    $alertsRepo->log((int)$rule['id'], $providerId, $tldId, $old, $new, $pct, $msg, $sent);
    $triggered++;

    echo ($sent ? 'SENT' : 'LOGGED') . ": {$providerName} {$tld} ({$pct}%)\n";
}

echo "Done. Triggered: {$triggered}\n";

