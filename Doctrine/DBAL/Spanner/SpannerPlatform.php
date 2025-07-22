<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\Exception\NotSupported;
use Doctrine\DBAL\Platforms\Keywords\KeywordList;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Spanner\SpannerKeywordList;
use OutOfBoundsException;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;

class SpannerPlatform extends AbstractPlatform {

    protected function createReservedKeywordsList(): KeywordList {
        return new SpannerKeywordList();
    }

    public function createSchemaManager(Connection $connection): AbstractSchemaManager {
        return new SpannerSchemaManager($connection, $connection->getDatabasePlatform());
    }

    public function getAlterTableSQL(TableDiff $diff): array {

        $queryParts = [];

        foreach ($diff->getAddedColumns() as $column) {
            $columnProperties = array_merge($column->toArray(), [
                'comment' => $column->getComment(),
            ]);

            $queryParts[] = 'ADD ' . $this->getColumnDeclarationSQL(
                $column->getQuotedName($this),
                $columnProperties,
            );
        }

        foreach ($diff->getDroppedColumns() as $column) {
            $queryParts[] =  'DROP ' . $column->getQuotedName($this);
        }

        foreach ($diff->getChangedColumns() as $columnDiff) {
            $newColumn = $columnDiff->getNewColumn();

            $newColumnProperties = array_merge($newColumn->toArray(), [
                'comment' => $newColumn->getComment(),
            ]);

            $oldColumn = $columnDiff->getOldColumn();

            $queryParts[] =  'ALTER ' . $oldColumn->getQuotedName($this) . ' '
                . $this->getColumnDeclarationSQL($newColumn->getQuotedName($this), $newColumnProperties);
        }

        $addedIndexes    = $diff->getAddedIndexes();
        $modifiedIndexes = $diff->getModifiedIndexes();
        $diffModified    = false;

        if (isset($addedIndexes['primary'])) {
            throw new NotSupported('Cannot modify primary keys of already created tables. Primary keys should be UUIDs anyways.');
        } elseif (isset($modifiedIndexes['primary'])) {
            throw new NotSupported('Cannot modify primary keys of already created tables. Primary keys should be UUIDs anyways.');
        }

        if ($diffModified) {
            $diff = new TableDiff(
                $diff->getOldTable(),
                addedColumns: $diff->getAddedColumns(),
                changedColumns: $diff->getChangedColumns(),
                droppedColumns: $diff->getDroppedColumns(),
                addedIndexes: array_values($addedIndexes),
                modifiedIndexes: array_values($modifiedIndexes),
                droppedIndexes: $diff->getDroppedIndexes(),
                renamedIndexes: $diff->getRenamedIndexes(),
                addedForeignKeys: $diff->getAddedForeignKeys(),
                modifiedForeignKeys: $diff->getModifiedForeignKeys(),
                droppedForeignKeys: $diff->getDroppedForeignKeys(),
            );
        }

        $tableSql = [];

        if (count($queryParts) > 0) {
            $tableSql[] = 'ALTER TABLE ' . $diff->getOldTable()->getQuotedName($this) . ' '
                . implode(', ', $queryParts);
        }

        return array_merge(
            $this->getPreAlterTableIndexForeignKeySQL($diff),
            $tableSql,
            $this->getPostAlterTableIndexForeignKeySQL($diff),
        );
    }

    public function getBigIntTypeDeclarationSQL(array $column): string {
        return 'INT64';
    }

    public function getBlobTypeDeclarationSQL(array $column): string {
        return 'BYTES(' . $column['length'] . ')';
    }

    public function getBooleanTypeDeclarationSQL(array $column): string {
        return 'BOOL';
    }
    
    public function getClobTypeDeclarationSQL(array $column): string {
        if($column['length'] > 2621440) {
            throw new OutOfBoundsException('CLOB length must be < 2621440');
        }
        return 'STRING(' . $column['length'] . ')';
    }
    
    public function getCurrentDatabaseExpression(): string {
        return "'' AS SCHEMA_NAME";
    }
    
    protected function getDateArithmeticIntervalExpression(string $date, string $operator, string $interval, \Doctrine\DBAL\Platforms\DateIntervalUnit $unit): string {
        $function = $operator === '+' ? 'DATE_ADD' : 'DATE_SUB';

        return $function . '(' . $date . ', MAKE_INTERVAL(' . $unit->value . ' => ' .$interval . ')';
    }
    
    public function getDateDiffExpression(string $date1, string $date2): string {
        return "DATE_DIFF(DATE ' . $date1 . ', DATE' . $date2 . ',  DAY' )";
    }
    
    public function getDateTimeTypeDeclarationSQL(array $column): string {
        return 'TIMESTAMP';
    }
    
    public function getDateTypeDeclarationSQL(array $column): string {
        return 'DATE';
    }
    
    public function getIntegerTypeDeclarationSQL(array $column): string {
        $autoinc = '';
        if (! empty($column['autoincrement'])) {
            throw new SpannerException('Autoincrement columns are not supported by Cloud Spanner. Use UUIDs instead.');
        }
        return 'INT64' ;
    }
    
    public function getListViewsSQL(string $database): string {
        return 'SELECT * FROM information_schema.VIEWS WHERE TABLE_SCHEMA = ' . $this->quoteStringLiteral($database);
    }

    public function getLocateExpression(string $string, string $substring, string|null $start = null): string {
        if ($start === null) {
            return sprintf('STRPOS(%s, %s)', $substring, $string);
        }

        return sprintf('STRPOS(SUBSTR(%s, %s), %s) + %s', $string, $substring, $start, $start);
    }

    public function getSetTransactionIsolationSQL(\Doctrine\DBAL\TransactionIsolationLevel $level): string {
        throw new UnsupportedException('Cloud Spanner has only one transaction isolation model', '', -1);
    }

    public function getSmallIntTypeDeclarationSQL(array $column): string {
        return 'INT64';
    }

    public function getTimeTypeDeclarationSQL(array $column): string {
        return 'TIMESTAMP';
    }

    public function initializeDoctrineTypeMappings(): void {
        $this->doctrineTypeMapping = [
            'array'      => Types::SIMPLE_ARRAY,
            'boolean'    => Types::BOOLEAN,
            'bytes'      => Types::BLOB,
            'date'       => Types::DATE_MUTABLE,
            'enum'       => Types::ENUM,
            'json'       => Types::JSON,
            'int64'      => Types::BIGINT,
            'numeric'    => Types::DECIMAL,
            'float32'    => Types::FLOAT,
            'float64'    => Types::FLOAT,
            'string'     => Types::STRING,
            'timestamp'  => Types::DATETIME_MUTABLE,
        ];
    }

    protected function _getCommonIntegerTypeDeclarationSQL(array $column): string {
        return 'INT64';
    }
}