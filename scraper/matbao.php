<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

/**
 * @param array<int, string> $tlds
 * @return array<string, float> map tld => price(VND)
 */
function scrape_matbao(array $tlds): array
{
    $url = 'https://www.matbao.net/ten-mien/bang-gia-ten-mien.html';
    $content = fetch_readable_page($url);
    return parse_markdown_table_prices($content, $tlds);
}

