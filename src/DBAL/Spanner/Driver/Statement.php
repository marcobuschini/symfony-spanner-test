<?php
namespace DBAL\Spanner\Driver;

use \Doctrine\DBAL\Driver\Statement as StatementInterface;
use \Doctrine\DBAL\ParameterType;
use \Doctrine\DBAL\Driver\Result as ResultInterface;

class Statement implements StatementInterface {

    public function bindValue(int|string $param, mixed $value, ParameterType $type): void {

    }

    public function execute(): ResultInterface {
        return new Result();
    }
}