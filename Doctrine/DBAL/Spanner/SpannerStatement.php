<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Driver\Result as ResultInterface;

class SpannerStatement implements Statement {

    private SpannerConnection $connection;
    private string $sql;
    private array $params;

    public function __construct(SpannerConnection $connection, string $sql, array $params) {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->params = $params;
    }

    public function bindValue(int|string $param, mixed $value, ParameterType $type): void {
        $this->params[$param] = $value;
    }

    public function execute(): ResultInterface {
        return $this->connection->query($this->sql);
    }
}