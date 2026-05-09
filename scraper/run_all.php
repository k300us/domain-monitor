<?php
declare(strict_types=1);

[$config, $db] = require __DIR__ . '/../src/bootstrap.php';
$pdo = $db->pdo();

require_once __DIR__ . '/pavietnam.php';
require_once __DIR__ . '/inet.php';
require_once __DIR__ . '/matbao.php';

$tldRepo = new TldRepository($pdo);
$providerRepo = new ProviderRepository($pdo);
$priceRepo = new PriceHistoryRepository($pdo);

$activeTlds = [];
foreach ($tldRepo->active() as $r) {
    $activeTlds[] = (string)$r['tld'];
}
if ($activeTlds === []) {
    fwrite(STDERR, "No active TLDs found in DB. Seed tlds first.\n");
    exit(1);
}

$providers = [
    'pavietnam' => ['fn' => 'scrape_pavietnam'],
    'inet' => ['fn' => 'scrape_inet'],
    'matbao' => ['fn' => 'scrape_matbao'],
];

foreach ($providers as $providerCode => $meta) {
    $provider = $providerRepo->findByCode($providerCode);
    if ($provider === null) {
        fwrite(STDERR, "Provider not found in DB: {$providerCode}\n");
        continue;
    }

    $fn = $meta['fn'];
    /** @var array<string, float> $prices */
    $prices = $fn($activeTlds);

    foreach ($activeTlds as $tld) {
        if (!isset($prices[$tld])) {
            continue;
        }
        $tldId = $tldRepo->upsert($tld, true);
        $raw = json_encode(['tld' => $tld, 'price' => $prices[$tld], 'provider' => $providerCode], JSON_UNESCAPED_UNICODE);
        $priceRepo->insert((int)$provider['id'], $tldId, (float)$prices[$tld], 'VND', $raw === false ? null : $raw);
    }

    echo "OK: {$providerCode} (" . count($prices) . " prices)\n";
}

echo "Done.\n";

