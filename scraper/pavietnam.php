<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

/**
 * @param array<int, string> $tlds
 * @return array<string, float> map tld => price(VND)
 */
function scrape_pavietnam(array $tlds): array
{
    $url = 'https://www.pavietnam.vn/vn/ten-mien-bang-gia.html';
    $content = fetch_readable_page($url);
    return parse_markdown_table_prices($content, $tlds);
}

