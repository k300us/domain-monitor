<?php
declare(strict_types=1);

final class Database
{
    /** @var \PDO */
    private $pdo;

    public function __construct(array $config)
    {
        $db = $config['db'] ?? [];
        $host = (string)($db['host'] ?? '127.0.0.1');
        $port = (int)($db['port'] ?? 3306);
        $name = (string)($db['name'] ?? '');
        $user = (string)($db['user'] ?? '');
        $pass = (string)($db['pass'] ?? '');
        $charset = (string)($db['charset'] ?? 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
        $this->pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function pdo()
    {
        return $this->pdo;
    }
}
