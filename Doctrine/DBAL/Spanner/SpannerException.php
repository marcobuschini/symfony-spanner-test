<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Driver\AbstractException;
use Throwable;

class SpannerException extends AbstractException {
    public function __construct(string $message, string|null $sqlState = null, int $code = 0, Throwable|null $previous = null) {
        parent::__construct($message, $sqlState, $code, $previous);
    }
}