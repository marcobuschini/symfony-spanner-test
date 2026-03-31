<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Driver\FetchUtils;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Exception\InvalidColumnIndex;
use Google\Cloud\Spanner\Result;

final class SpannerResult implements ResultInterface
{
    private ?Result $result;

    /** @internal The result can be only instantiated by its driver connection or statement. */
    public function __construct(Result $result, private readonly int $changes)
    {
        $this->result = $result;
    }

    public function fetchNumeric(): array|false
    {
        if ($this->result === null) {
            return false;
        }

        $rows =  $this->result->rows(Result::RETURN_ZERO_INDEXED);

        return $rows->current();
    }

    public function fetchAssociative(): array|false
    {
        if ($this->result === null) {
            return false;
        }

        $rows =  $this->result->rows(Result::RETURN_ASSOCIATIVE);

        return $rows->current();
    }

    public function fetchOne(): mixed
    {
        return FetchUtils::fetchOne($this);
    }

    /** @inheritDoc */
    public function fetchAllNumeric(): array
    {
        return FetchUtils::fetchAllNumeric($this);
    }

    /** @inheritDoc */
    public function fetchAllAssociative(): array
    {
        return FetchUtils::fetchAllAssociative($this);
    }

    /** @inheritDoc */
    public function fetchFirstColumn(): array
    {
        return FetchUtils::fetchFirstColumn($this);
    }

    public function rowCount(): int
    {
        return $this->changes;
    }

    public function columnCount(): int
    {
        if ($this->result === null) {
            return 0;
        }

        return count($this->result->columns());
    }

    public function getColumnName(int $index): string
    {
        if ($this->result === null) {
            throw InvalidColumnIndex::new($index);
        }

        $columns = $this->result->columns();

        if ($columns === false || count($columns) < $index) {
            throw InvalidColumnIndex::new($index);
        }

        return $columns[$index];
    }

    public function free(): void
    {
        if ($this->result === null) {
            return;
        }

        $this->result = null;
    }
}
