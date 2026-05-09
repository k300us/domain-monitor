<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

/**
 * @param array<int, string> $tlds
 * @return array<string, float> map tld => price(VND)
 */
function scrape_inet(array $tlds): array
{
    $json = http_get('https://inet.vn/api/domain/listsuffix');
    $rows = json_decode($json, true);
    if (!is_array($rows)) {
        throw new RuntimeException('Invalid iNet API response.');
    }

    $wanted = [];
    foreach ($tlds as $tld) {
        $wanted[normalize_tld($tld)] = true;
    }

    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $suffix = isset($row['suffix']) ? normalize_tld((string)$row['suffix']) : '';
        if ($suffix === '' || !isset($wanted[$suffix])) {
            continue;
        }

        $regPromo = isset($row['regPromotion']) ? (float)$row['regPromotion'] : 0.0;
        $regOrigin = isset($row['regOrigin']) ? (float)$row['regOrigin'] : 0.0;
        $price = $regPromo > 0 ? $regPromo : $regOrigin;
        if ($price > 0) {
            $out[$suffix] = $price;
        }
    }

    return $out;
}

