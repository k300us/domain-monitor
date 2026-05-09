<?php
declare(strict_types=1);

final class TldRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return array<int, array{id:int, tld:string, is_active:int}> */
    public function active(): array
    {
        return $this->pdo->query('SELECT id, tld, is_active FROM tlds WHERE is_active = 1 ORDER BY tld ASC')
            ->fetchAll();
    }

    public function upsert(string $tld, bool $isActive = true): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO tlds (tld, is_active) VALUES (:tld, :is_active)
             ON DUPLICATE KEY UPDATE is_active = VALUES(is_active), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute(['tld' => $tld, 'is_active' => $isActive ? 1 : 0]);

        $stmt2 = $this->pdo->prepare('SELECT id FROM tlds WHERE tld = :tld LIMIT 1');
        $stmt2->execute(['tld' => $tld]);
        return (int)$stmt2->fetchColumn();
    }
}

