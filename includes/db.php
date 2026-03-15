<?php

declare(strict_types=1);

$servername = "localhost";
$username = "icto_randy3";
$password = "091902Rands..";
$dbname = "Ace-mtech";

$conn = new mysqli($servername, $username, $password, $dbname);

$appBaseUrl = getenv("APP_BASE_URL");
if (!empty($appBaseUrl)) {
    define("APP_BASE_URL", rtrim($appBaseUrl, "/"));
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("latin1")) {
    die("Error setting charset: " . $conn->error);
}

final class MysqliStatementCompat
{
    private mysqli $conn;
    private string $originalSql;
    private string $parsedSql;
    /** @var string[] */
    private array $namedOrder;
    private ?mysqli_stmt $stmt = null;
    private ?mysqli_result $result = null;

    public function __construct(mysqli $conn, string $sql)
    {
        $this->conn = $conn;
        $this->originalSql = $sql;
        [$this->parsedSql, $this->namedOrder] = $this->parseNamedPlaceholders($sql);
    }

    public function execute(array $params = []): bool
    {
        $this->result = null;

        $stmt = $this->conn->prepare($this->parsedSql);
        if (!$stmt) {
            return false;
        }

        $orderedParams = $this->normalizeParams($params);
        if ($orderedParams !== []) {
            [$types, $values] = $this->buildBindPayload($orderedParams);
            $refs = [$types];
            foreach ($values as $index => $value) {
                $refs[] = &$values[$index];
            }
            if (!call_user_func_array([$stmt, "bind_param"], $refs)) {
                $stmt->close();
                return false;
            }
        }

        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $this->result = $result instanceof mysqli_result ? $result : null;
        $this->stmt = $stmt;

        return true;
    }

    public function fetch()
    {
        if (!$this->result) {
            return false;
        }

        $row = $this->result->fetch_assoc();
        return $row !== null ? $row : false;
    }

    public function fetchAll(): array
    {
        if (!$this->result) {
            return [];
        }

        $rows = $this->result->fetch_all(MYSQLI_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    public function fetchColumn(int $column = 0)
    {
        $row = $this->fetch();
        if ($row === false) {
            return false;
        }

        $values = array_values($row);
        return $values[$column] ?? false;
    }

    private function parseNamedPlaceholders(string $sql): array
    {
        $names = [];
        $parsed = preg_replace_callback(
            "/:([a-zA-Z_][a-zA-Z0-9_]*)/",
            static function (array $matches) use (&$names): string {
                $names[] = $matches[1];
                return "?";
            },
            $sql
        );

        return [is_string($parsed) ? $parsed : $sql, $names];
    }

    private function normalizeParams(array $params): array
    {
        if ($params === []) {
            return [];
        }

        $usesNamed = $this->namedOrder !== [];
        if (!$usesNamed) {
            return array_values($params);
        }

        $ordered = [];
        foreach ($this->namedOrder as $name) {
            $keyWithColon = ":" . $name;
            if (array_key_exists($keyWithColon, $params)) {
                $ordered[] = $params[$keyWithColon];
                continue;
            }

            $ordered[] = $params[$name] ?? null;
        }

        return $ordered;
    }

    private function buildBindPayload(array $orderedParams): array
    {
        $types = "";
        $values = [];

        foreach ($orderedParams as $value) {
            if (is_bool($value)) {
                $types .= "i";
                $values[] = $value ? 1 : 0;
                continue;
            }

            if (is_int($value)) {
                $types .= "i";
                $values[] = $value;
                continue;
            }

            if (is_float($value)) {
                $types .= "d";
                $values[] = $value;
                continue;
            }

            $types .= "s";
            $values[] = $value;
        }

        return [$types, $values];
    }

    public function __destruct()
    {
        if ($this->stmt instanceof mysqli_stmt) {
            $this->stmt->close();
        }
    }
}

final class MysqliPdoCompat
{
    private mysqli $conn;
    private bool $inTransaction = false;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function query(string $sql): MysqliStatementCompat
    {
        $stmt = new MysqliStatementCompat($this->conn, $sql);
        $stmt->execute();
        return $stmt;
    }

    public function prepare(string $sql): MysqliStatementCompat
    {
        return new MysqliStatementCompat($this->conn, $sql);
    }

    public function exec(string $sql): int
    {
        $ok = $this->conn->query($sql);
        if ($ok === false) {
            return 0;
        }

        return $this->conn->affected_rows;
    }

    public function quote(string $value): string
    {
        return "'" . $this->conn->real_escape_string($value) . "'";
    }

    public function beginTransaction(): bool
    {
        $this->inTransaction = $this->conn->begin_transaction();
        return $this->inTransaction;
    }

    public function commit(): bool
    {
        $ok = $this->conn->commit();
        if ($ok) {
            $this->inTransaction = false;
        }
        return $ok;
    }

    public function rollBack(): bool
    {
        $ok = $this->conn->rollback();
        if ($ok) {
            $this->inTransaction = false;
        }
        return $ok;
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function lastInsertId(): string
    {
        return (string)$this->conn->insert_id;
    }
}

function conn(): mysqli
{
    global $conn;
    return $conn;
}

function db(): MysqliPdoCompat
{
    static $adapter = null;
    if ($adapter === null) {
        $adapter = new MysqliPdoCompat(conn());
    }

    return $adapter;
}
