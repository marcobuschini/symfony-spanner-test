<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Driver\Connection as DoctrineConnectionInterface;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Connection\ConnectionInterface as SpannerConnectionInterface;

final class SpannerConnection implements DoctrineConnectionInterface
{
    private SpannerClient $spanner;
    private Instance $instance;
    private Database $database;
    private SpannerConnectionInterface $connection;

    public function __construct(string $projectId, string $instanceId, string $databaseId) {
        $this->spanner = new SpannerClient(['projectId' => $projectId]);
        $this->instance = $this->spanner->instance($instanceId);
        $this->database = $this->instance->database($databaseId);
        $this->connection = $this->database->connection();
    }

    public function __destruct() {
        $this->database->close();
    }

    public function beginTransaction(): void {
        throw new SpannerException('With Cloud Spanner you must use functional syntax for transactions.');
    }

    public function commit(): void {
        throw new SpannerException('With Cloud Spanner you must use functional syntax for transactions.');
    }

    public function rollBack(): void {
        throw new SpannerException('With Cloud Spanner you must use functional syntax for transactions.');
    }

    public function exec(string $sql): int|string {
        return $this->query($sql)->rowCount();
    }

    public function getNativeConnection() {
        return $this->connection;
    }

    public function lastInsertId(): int|string {
        throw new SpannerException('lastInsertId is not supported by Cloud Spanner');
    }

    public function prepare(string $sql): SpannerStatement {
        return new SpannerStatement($this, $sql, []);
    }

    public function query(string $sql): SpannerResult {
        return new SpannerResult($this->database->execute($sql), 0);
    }

    public function quote(string $value): string {
        return "`$value`";
    }

    public function getServerVersion(): string {
        return '0.1';
    }
}
