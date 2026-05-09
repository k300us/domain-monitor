<?php
declare(strict_types=1);

/**
 * @return string
 */
function http_get(string $url, int $timeout = 25)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_USERAGENT => 'Mozilla/5.0 DomainMonitor/1.0',
        CURLOPT_HTTPHEADER => ['Accept: text/html,application/json;q=0.9,*/*;q=0.8'],
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($body === false || $status < 200 || $status >= 400) {
        throw new RuntimeException('Fetch failed [' . $status . '] ' . $url . ' ' . $err);
    }

    return (string)$body;
}

/**
 * Proxy to convert complex pages into markdown-like text (easier to parse).
 */
function fetch_readable_page(string $url)
{
    $proxyUrl = 'https://r.jina.ai/http://' . preg_replace('#^https?://#i', '', $url);
    try {
        return http_get($proxyUrl, 30);
    } catch (Throwable $e) {
        return http_get($url, 30);
    }
}

function normalize_tld(string $tld)
{
    $tld = strtolower(trim($tld));
    if ($tld === '') {
        return '';
    }
    if ($tld[0] !== '.') {
        $tld = '.' . $tld;
    }
    return $tld;
}

/**
 * @return array<int, int>
 */
function extract_vnd_values(string $text)
{
    $values = [];
    if (preg_match_all('/(\d{1,3}(?:[.\s]\d{3})+|\d+)\s*(?:đ|vnd)?/iu', $text, $m)) {
        foreach ($m[1] as $raw) {
            $n = (int)preg_replace('/[^\d]/', '', $raw);
            if ($n > 0) {
                $values[] = $n;
            }
        }
    }
    return $values;
}

/**
 * Pick registration-like price from a row's registration cell.
 */
function pick_price_from_cell(string $cell)
{
    if (preg_match('/Tổng:\s*\*?\*?\s*([\d\.\s,]+)\s*đ/iu', $cell, $m)) {
        $v = (int)preg_replace('/[^\d]/', '', $m[1]);
        if ($v > 0) {
            return (float)$v;
        }
    }

    $values = extract_vnd_values($cell);
    if ($values === []) {
        return null;
    }

    // Promotion price is usually the lower value in registration column.
    return (float)min($values);
}

/**
 * Parse markdown-like table lines where each row starts with "| ... |".
 *
 * @param array<int,string> $tlds
 * @return array<string,float>
 */
function parse_markdown_table_prices(string $text, array $tlds)
{
    $wanted = [];
    foreach ($tlds as $tld) {
        $wanted[normalize_tld($tld)] = true;
    }

    $prices = [];
    $lines = preg_split('/\R/u', $text) ?: [];
    foreach ($lines as $line) {
        if (strpos($line, '|') === false) {
            continue;
        }

        foreach ($wanted as $tld => $_) {
            if ($tld === '' || isset($prices[$tld])) {
                continue;
            }

            $pattern = '/' . preg_quote($tld, '/') . '(?![a-z0-9])/i';
            if (!preg_match($pattern, $line)) {
                continue;
            }

            $cells = explode('|', $line);
            $registrationCell = isset($cells[2]) ? (string)$cells[2] : $line;
            $price = pick_price_from_cell($registrationCell);
            if ($price !== null) {
                $prices[$tld] = $price;
            }
        }
    }

    return $prices;
}

