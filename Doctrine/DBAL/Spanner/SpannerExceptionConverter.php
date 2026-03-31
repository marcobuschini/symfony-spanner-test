<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Driver\API\ExceptionConverter as ExceptionConverterInterface;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Query;
use Doctrine\DBAL\Exception\DriverException;

class SpannerExceptionConverter implements ExceptionConverterInterface {

    public function convert(Exception $exception, Query|null $query): DriverException {
        return new DriverException($exception, $query);
    }
}