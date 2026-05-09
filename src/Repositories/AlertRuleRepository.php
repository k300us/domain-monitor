<?php
declare(strict_types=1);

final class AlertRuleRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return array<int, array{id:int, provider_id:int, tld_id:int, percent_threshold:float, is_enabled:int}> */
    public function enabledRules(): array
    {
        $sql = 'SELECT id, provider_id, tld_id, percent_threshold, is_enabled
                FROM alert_rules
                WHERE is_enabled = 1
                ORDER BY id ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(int $providerId, int $tldId, float $percentThreshold): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO alert_rules (provider_id, tld_id, percent_threshold, is_enabled)
             VALUES (:provider_id, :tld_id, :percent_threshold, 1)'
        );
        $stmt->execute([
            'provider_id' => $providerId,
            'tld_id' => $tldId,
            'percent_threshold' => $percentThreshold,
        ]);
    }
}

