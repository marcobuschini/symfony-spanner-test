<?php

use \Doctrine\DBAL\Driver\Result as ResultInterface;

class Result implements ResultInterface {
    function columnCount(): int {
        return null;
    }

    function fetchAllAssociative(): array {
        return null;
    }

    function fetchAllNumeric(): array {
        return null;
    }

    function fetchAssociative(): array|bool {
        return null;
    }

    function fetchFirstColumn(): array {
        return null;
    }

    function fetchNumeric(): array|bool {
        return null;
    }

    function fetchOne(): mixed {
        return null;
    }

    function free(): void {
        return null;
    }

    function rowCount(): int|string {
        return null;
    }
}