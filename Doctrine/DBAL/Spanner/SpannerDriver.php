<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter as ExceptionConverterInterface;
use Doctrine\DBAL\ServerVersionProvider;
use SensitiveParameter;

final class SpannerDriver implements Driver
{
    // @type SpannerConnection
    private $connection;

    // @type SpannerPlatform
    private $platform = null;

    public function getDatabasePlatform(ServerVersionProvider $versionProvider): SpannerPlatform
    {
        if ($this->platform === null) {
            $this->platform = new SpannerPlatform();
        }
        return $this->platform;
    }

    public function getExceptionConverter(): ExceptionConverterInterface
    {
        return new SpannerExceptionConverter();
    }
    /**
     * {@inheritDoc}
     */
    public function connect(
        #[SensitiveParameter]
        array $params,
    ): SpannerConnection {
        if($this->connection === null) {
            $this->connection = new SpannerConnection($params['driverOptions']['projectId'], $params['driverOptions']['instanceId'], $params['driverOptions']['databaseId']);
        }
        return $this->connection;
    }
}
