<?php

namespace DBAL\Spanner;

class SchemaManager extends \Doctrine\DBAL\Schema\AbstractSchemaManager {
    protected function fetchTableOptionsByTable(string $databaseName, string|null $tableName = null): array {
        return null;
    }

    protected function selectForeignKeyColumns(string $databaseName, string|null $tableName = null): \Doctrine\DBAL\Result {
        return null;
    }

    protected function selectIndexColumns(string $databaseName, string|null $tableName = null): \Doctrine\DBAL\Result {
        return null;
    }

    protected function selectTableColumns(string $databaseName, string|null $tableName = null): \Doctrine\DBAL\Result {
        return null;
    }

    protected function selectTableNames(string $databaseName): \Doctrine\DBAL\Result {
        return null;
    }

    protected function _getPortableTableColumnDefinition(array $tableColumn): \Doctrine\DBAL\Schema\Column {
        return null;
    }

    protected function _getPortableTableDefinition(array $table): string {
        return null;
    }

    protected function _getPortableTableForeignKeyDefinition(array $tableForeignKey): \Doctrine\DBAL\Schema\ForeignKeyConstraint {
        return null;
    }

    protected function _getPortableViewDefinition(array $view): \Doctrine\DBAL\Schema\View {
        return null;
    }
}