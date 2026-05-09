<?php
declare(strict_types=1);

final class AlertsLogRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(
        int $ruleId,
        int $providerId,
        int $tldId,
        float $oldPrice,
        float $newPrice,
        float $percentChange,
        string $message,
        bool $sent
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO alerts_log (rule_id, provider_id, tld_id, old_price, new_price, percent_change, message, sent_to_telegram, created_at)
             VALUES (:rule_id, :provider_id, :tld_id, :old_price, :new_price, :percent_change, :message, :sent_to_telegram, NOW())'
        );
        $stmt->execute([
            'rule_id' => $ruleId,
            'provider_id' => $providerId,
            'tld_id' => $tldId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'percent_change' => $percentChange,
            'message' => $message,
            'sent_to_telegram' => $sent ? 1 : 0,
        ]);
    }

    /** @return array<int, array<string,mixed>> */
    public function latest(int $limit = 50): array
    {
        $sql = '
            SELECT al.created_at, p.name AS provider_name, t.tld, al.old_price, al.new_price, al.percent_change, al.message, al.sent_to_telegram
            FROM alerts_log al
            JOIN providers p ON p.id = al.provider_id
            JOIN tlds t ON t.id = al.tld_id
            ORDER BY al.created_at DESC
            LIMIT ' . (int)$limit;
        return $this->pdo->query($sql)->fetchAll();
    }
}

