<?php

class Database
{
    private mysqli $connection;
    private string $logTable;

    public function __construct(string $host, string $username, string $password, string $database, string $logTable)
    {
        $this->connection = new mysqli($host, $username, $password, $database);
        if ($this->connection->connect_error) {
            http_response_code(500);
            die('Database connection failed');
        }

        $this->connection->set_charset('utf8mb4');
        $this->logTable = $logTable;
    }

    public function connection(): mysqli
    {
        return $this->connection;
    }

    public function fetchOne(string $sql, string $types = '', mixed ...$params): ?array
    {
        $stmt = $this->prepareAndExecute($sql, $types, ...$params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function fetchAll(string $sql, string $types = '', mixed ...$params): array
    {
        $stmt = $this->prepareAndExecute($sql, $types, ...$params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function execute(string $sql, string $types = '', mixed ...$params): mysqli_stmt
    {
        return $this->prepareAndExecute($sql, $types, ...$params);
    }

    public function insertId(): int
    {
        return $this->connection->insert_id;
    }

    public function log(string $query, ?string $user = null): void
    {
        $user = $user ?: ($_SESSION['auth'] ?? 'Public');
        $stmt = $this->connection->prepare("INSERT INTO `{$this->logTable}` (query, user) VALUES (?, ?)");
        $stmt->bind_param('ss', $query, $user);
        $stmt->execute();
    }

    private function prepareAndExecute(string $sql, string $types = '', mixed ...$params): mysqli_stmt
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            http_response_code(500);
            die('Failed to prepare database query');
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }
}
