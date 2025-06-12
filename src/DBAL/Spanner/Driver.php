<?php

namespace DBAL\Spanner;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\ServerVersionProvider;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class Driver implements DriverInterface {

    public function connect(array $params): Connection {
        return null;
    }

    public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform {
        return null;
    }

    public function getExceptionConverter(): ExceptionConverter {
        return null;
    }
}