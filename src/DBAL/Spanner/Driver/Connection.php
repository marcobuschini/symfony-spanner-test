<?php
namespace DBAL\Spanner\Driver;

use \Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use \Doctrine\DBAL\Driver\Statement as StatementInterface;
use \Doctrine\DBAL\Driver\Result as ResultInterface;



class Connection implements ConnectionInterface {

    public function beginTransaction(): void {

    }

    public function commit(): void {

    }

    public function rollBack(): void {

    }

    public function exec(string $sql): int|string {
        return 0;
    }

    public function getNativeConnection() {

    }

    public function lastInsertId(): int|string {
        return 0;
    }

    public function prepare(string $sql): StatementInterface {
        return new Statement();
    }

    public function query(string $sql): ResultInterface {
        return new Result();
    }

    public function quote(string $value): string {
        return $value;
    }

    public function getServerVersion(): string {
        return '0.1';
    }
}
