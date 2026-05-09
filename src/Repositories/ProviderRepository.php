<?php
declare(strict_types=1);

final class ProviderRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** @return array<int, array{id:int, code:string, name:string, base_url:?string, is_primary:int}> */
    public function all(): array
    {
        return $this->pdo->query('SELECT id, code, name, base_url, is_primary FROM providers ORDER BY is_primary DESC, name ASC')
            ->fetchAll();
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, code, name, base_url, is_primary FROM providers WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
}

