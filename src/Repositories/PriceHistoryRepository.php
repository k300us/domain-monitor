<?php
declare(strict_types=1);

final class PriceHistoryRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function insert(int $providerId, int $tldId, float $priceVnd, string $currency = 'VND', ?string $rawSource = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO price_history (provider_id, tld_id, price, currency, raw_source, scraped_at)
             VALUES (:provider_id, :tld_id, :price, :currency, :raw_source, NOW())'
        );
        $stmt->execute([
            'provider_id' => $providerId,
            'tld_id' => $tldId,
            'price' => $priceVnd,
            'currency' => $currency,
            'raw_source' => $rawSource,
        ]);
    }

    /** @return array<int, array{price:float, scraped_at:string}> */
    public function lastN(int $providerId, int $tldId, int $n = 2): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT price, scraped_at
             FROM price_history
             WHERE provider_id = :provider_id AND tld_id = :tld_id
             ORDER BY scraped_at DESC
             LIMIT ' . (int)$n
        );
        $stmt->execute(['provider_id' => $providerId, 'tld_id' => $tldId]);
        return $stmt->fetchAll();
    }

    /** @return array<int, array{provider_code:string, provider_name:string, tld:string, price:float, scraped_at:string}> */
    public function latestPricesForActiveTlds(): array
    {
        $sql = '
            SELECT p.code AS provider_code, p.name AS provider_name, t.tld, ph.price, ph.scraped_at
            FROM price_history ph
            JOIN providers p ON p.id = ph.provider_id
            JOIN tlds t ON t.id = ph.tld_id
            JOIN (
                SELECT provider_id, tld_id, MAX(scraped_at) AS max_scraped
                FROM price_history
                GROUP BY provider_id, tld_id
            ) latest ON latest.provider_id = ph.provider_id AND latest.tld_id = ph.tld_id AND latest.max_scraped = ph.scraped_at
            WHERE t.is_active = 1
            ORDER BY t.tld ASC, p.is_primary DESC, p.name ASC
        ';
        return $this->pdo->query($sql)->fetchAll();
    }

    /** @return array<int, array{provider_name:string, tld:string, price:float, scraped_at:string}> */
    public function history(string $tld, ?string $providerCode, int $limit = 200): array
    {
        $params = ['tld' => $tld];
        $where = 't.tld = :tld';
        if ($providerCode !== null && $providerCode !== '') {
            $where .= ' AND p.code = :code';
            $params['code'] = $providerCode;
        }

        $stmt = $this->pdo->prepare(
            "SELECT p.name AS provider_name, t.tld, ph.price, ph.scraped_at
             FROM price_history ph
             JOIN providers p ON p.id = ph.provider_id
             JOIN tlds t ON t.id = ph.tld_id
             WHERE {$where}
             ORDER BY ph.scraped_at DESC
             LIMIT " . (int)$limit
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

