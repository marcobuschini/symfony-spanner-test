<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Schema\View;
use Doctrine\DBAL\Spanner\SpannerException;

class SpannerSchemaManager extends \Doctrine\DBAL\Schema\AbstractSchemaManager
{
    protected function determineCurrentSchemaName(): ?string {
        return '';
    }
    
    protected function fetchTableOptionsByTable(string $databaseName, ?string $tableName = null): array
    {
        $sql = $this->platform->fetchTableOptionsByTable($tableName !== null);

        $params = [$databaseName];
        if ($tableName !== null) {
            $params[] = $tableName;
        }

        /** @var array<string,array<string,mixed>> $metadata */
        $metadata = $this->connection->executeQuery($sql, $params)
            ->fetchAllAssociativeIndexed();

        $tableOptions = [];
        foreach ($metadata as $table => $data) {
            $data = array_change_key_case($data, CASE_LOWER);

            $tableOptions[$table] = [
                'allow_commit_timestamp' => $data['allow_commit_timestamp'],
                'locality_group' => $data['locality_group'],
                'autoincrement' => $data['auto_increment'],
                'generated' => $data['generated'],
            ];
        }

        return $tableOptions;
    }

    protected function selectForeignKeyColumns(string $databaseName, string|null $tableName = null): \Doctrine\DBAL\Result
    {
        $query = <<<SQL
SELECT
    rc.CONSTRAINT_NAME,
    rc.UNIQUE_CONSTRAINT_NAME AS primary_key_constraint_name,
    rc.MATCH_OPTION, -- NONE, FULL, PARTIAL
    rc.UPDATE_RULE,  -- NO ACTION, RESTRICT, CASCADE, SET NULL, SET DEFAULT
    rc.DELETE_RULE,  -- NO ACTION, RESTRICT, CASCADE, SET NULL, SET DEFAULT
    kcu_referencing.TABLE_NAME AS referencing_table,
    kcu_referencing.COLUMN_NAME AS referencing_column,
    kcu_referencing.ORDINAL_POSITION AS referencing_column_position,
    -- Referenced table and columns (parent table)
    kcu_referenced.TABLE_NAME AS referenced_table,
    kcu_referenced.COLUMN_NAME AS referenced_column,
    kcu_referenced.ORDINAL_POSITION AS referenced_column_position
FROM
    INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS rc
JOIN
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS kcu_referencing
    ON rc.CONSTRAINT_NAME = kcu_referencing.CONSTRAINT_NAME
    AND rc.CONSTRAINT_SCHEMA = kcu_referencing.CONSTRAINT_SCHEMA
    AND rc.CONSTRAINT_CATALOG = kcu_referencing.CONSTRAINT_CATALOG
JOIN
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS kcu_referenced
    ON rc.UNIQUE_CONSTRAINT_NAME = kcu_referenced.CONSTRAINT_NAME
    AND rc.UNIQUE_CONSTRAINT_SCHEMA = kcu_referenced.CONSTRAINT_SCHEMA
    AND rc.UNIQUE_CONSTRAINT_CATALOG = kcu_referenced.CONSTRAINT_CATALOG
WHERE
    rc.CONSTRAINT_CATALOG = ''
    AND rc.CONSTRAINT_SCHEMA = ''
SQL;

        $whereParts = [];
        $params = [];
        if ($tableName) {
            $whereParts[] = 'kcu_referencing.TABLE_NAME = ?tableName;';
            $params['tableName'] = $tableName;
        }
        if ($databaseName) {
            $whereParts[] = 'kcu_referencing.SCHEMA_NAME = ?schemaName;';
            $params['schemaName'] = $databaseName;
        }

        $where = join($whereParts, ' AND ');

        if ($where) {
            $query .= ' WHERE ' . $where;
        }

        $result = $this->connection->executeQuery($query, $params);

        return $result;
    }

    protected function selectIndexColumns(string $databaseName, string|null $tableName = null): \Doctrine\DBAL\Result
    {
        $query = <<<SQL
SELECT
    i.TABLE_SCHEMA as SCHEMA_NAME,
    i.TABLE_NAME,
    i.INDEX_NAME as RELNAME,
    i.IS_UNIQUE as INDISUNIQUE,
    i.INDEX_TYPE = "PRIMARY_KEY" as INDISPRIMARY,
    ic.COLUMN_NAME,
    ic.ORDINAL_POSITION,
    ic.COLUMN_ORDERING
FROM
    INFORMATION_SCHEMA.INDEXES AS i
JOIN
    INFORMATION_SCHEMA.INDEX_COLUMNS AS ic
ON
    i.TABLE_SCHEMA = ic.TABLE_SCHEMA AND
    i.TABLE_NAME = ic.TABLE_NAME AND
    i.INDEX_NAME = ic.INDEX_NAME
SQL;

        $whereParts = [];
        $params = [];
        if ($tableName) {
            $whereParts[] = 'i.TABLE_NAME = ?tableName;';
            $params['tableName'] = $tableName;
        }
        if ($databaseName) {
            $whereParts[] = 'i.TABLE_SCHEMA = ?schemaName;';
            $params['schemaName'] = $databaseName;
        }

        $where = join($whereParts, ' AND ');

        if ($where) {
            $query .= ' WHERE ' . $where;
        }

        $result = $this->connection->executeQuery($query, $params);

        return $result;
    }

    protected function selectTableColumns(string $databaseName, string|null $tableName = null): \Doctrine\DBAL\Result
    {
        $params = ['schema' => $databaseName];
        $query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ?schema';

        if($tableName) {
            $query .= ' AND TABLE_NAME = ?name';
            $params['name'] = $tableName;
        }
        $query .= ';';

        $result = $this->connection->executeQuery($query, $params);

        return $result;
    }

    protected function selectTableNames(string $databaseName): \Doctrine\DBAL\Result
    {
        $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES";

        if($databaseName) {
            $query .= ' WHERE TABLE_SCHEMA = ?name';
        }
        $query .= ';';

        $result = $this->connection->executeQuery($query, [$databaseName]);

        return $result;
    }

    protected function _getPortableTableColumnDefinition(array $tableColumn): Column
    {
        $tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

        $length = null;

        if (
            in_array(strtolower($tableColumn['type']), ['varchar', 'bpchar'], true)
            && preg_match('/\((\d*)\)/', $tableColumn['complete_type'], $matches) === 1
        ) {
            $length = (int) $matches[1];
        }

        $autoincrement = $tableColumn['attidentity'] === 'd';

        $matches = [];

        assert(array_key_exists('default', $tableColumn));
        assert(array_key_exists('complete_type', $tableColumn));

        if ($tableColumn['default'] !== null) {
            if (preg_match("/^['(](.*)[')]::/", $tableColumn['default'], $matches) === 1) {
                $tableColumn['default'] = $matches[1];
            } elseif (preg_match('/^NULL::/', $tableColumn['default']) === 1) {
                $tableColumn['default'] = null;
            }
        }

        if ($length === -1 && isset($tableColumn['atttypmod'])) {
            $length = $tableColumn['atttypmod'] - 4;
        }

        if ((int) $length <= 0) {
            $length = null;
        }

        $fixed = false;

        if (! isset($tableColumn['name'])) {
            $tableColumn['name'] = '';
        }

        $precision = null;
        $scale     = 0;
        $jsonb     = null;

        $dbType = strtolower($tableColumn['type']);
        if (
            $tableColumn['domain_type'] !== null
            && $tableColumn['domain_type'] !== ''
            && ! $this->platform->hasDoctrineTypeMappingFor($tableColumn['type'])
        ) {
            $dbType                       = strtolower($tableColumn['domain_type']);
            $tableColumn['complete_type'] = $tableColumn['domain_complete_type'];
        }

        $type = $this->platform->getDoctrineTypeMapping($dbType);

        switch ($dbType) {
            case 'smallint':
            case 'int2':
            case 'int':
            case 'int4':
            case 'integer':
            case 'bigint':
            case 'int8':
                $length = null;
                break;

            case 'bool':
            case 'boolean':
                if ($tableColumn['default'] === 'true') {
                    $tableColumn['default'] = true;
                }

                if ($tableColumn['default'] === 'false') {
                    $tableColumn['default'] = false;
                }

                $length = null;
                break;

            case 'json':
            case 'text':
            case 'varchar':
                $tableColumn['default'] = $this->parseDefaultExpression($tableColumn['default']);
                break;

            case 'char':
            case 'bpchar':
                $fixed = true;
                break;

            case 'float':
            case 'float4':
            case 'float8':
            case 'double':
            case 'double precision':
            case 'real':
            case 'decimal':
            case 'money':
            case 'numeric':
                if (
                    preg_match(
                        '([A-Za-z]+\(([0-9]+),([0-9]+)\))',
                        $tableColumn['complete_type'],
                        $match,
                    ) === 1
                ) {
                    $precision = (int) $match[1];
                    $scale     = (int) $match[2];
                    $length    = null;
                }

                break;

            case 'year':
                $length = null;
                break;

            // PostgreSQL 9.4+ only
            case 'jsonb':
                $jsonb = true;
                break;
        }

        if (
            is_string($tableColumn['default']) && preg_match(
                "('([^']+)'::)",
                $tableColumn['default'],
                $match,
            ) === 1
        ) {
            $tableColumn['default'] = $match[1];
        }

        $options = [
            'length'        => $length,
            'notnull'       => (bool) $tableColumn['isnotnull'],
            'default'       => $tableColumn['default'],
            'precision'     => $precision,
            'scale'         => $scale,
            'fixed'         => $fixed,
            'autoincrement' => $autoincrement,
        ];

        if (isset($tableColumn['comment'])) {
            $options['comment'] = $tableColumn['comment'];
        }

        $column = new Column($tableColumn['field'], Type::getType($type), $options);

        if (! empty($tableColumn['collation'])) {
            $column->setPlatformOption('collation', $tableColumn['collation']);
        }

        if ($column->getType() instanceof JsonType) {
            $column->setPlatformOption('jsonb', $jsonb);
        }

        return $column;
    }

    private function parseDefaultExpression(?string $default): ?string
    {
        if ($default === null) {
            return $default;
        }

        return str_replace("''", "'", $default);
    }

    protected function _getPortableTableDefinition(array $table): string
    {
        return $table['table_name'];
    }

    protected function _getPortableTableForeignKeyDefinition(array $tableForeignKey): ForeignKeyConstraint
    {
        $onUpdate = null;
        $onDelete = null;

        if (
            preg_match(
                '(ON UPDATE ([a-zA-Z0-9]+( (NULL|ACTION|DEFAULT))?))',
                $tableForeignKey['condef'],
                $match,
            ) === 1
        ) {
            throw new SpannerException('ON UPDATE not suopported on foreign keys as primary kes are supposed to be immudable');
        }

        if (
            preg_match(
                '(ON DELETE ([a-zA-Z0-9]+( (NULL|ACTION|DEFAULT))?))',
                $tableForeignKey['condef'],
                $match,
            ) === 1
        ) {
            throw new SpannerException('ON DELETE not suopported on foreign keys as primary keys are supposd to be immutable');
        }

        $result = preg_match('/FOREIGN KEY \((.+)\) REFERENCES (.+)\((.+)\)/', $tableForeignKey['condef'], $values);
        assert($result === 1);

        // PostgreSQL returns identifiers that are keywords with quotes, we need them later, don't get
        // the idea to trim them here.
        $localColumns   = array_map('trim', explode(',', $values[1]));
        $foreignColumns = array_map('trim', explode(',', $values[3]));
        $foreignTable   = $values[2];

        return new ForeignKeyConstraint(
            $localColumns,
            $foreignTable,
            $foreignColumns,
            $tableForeignKey['conname'],
            ['onUpdate' => $onUpdate, 'onDelete' => $onDelete],
        );
    }

    protected function _getPortableViewDefinition(array $view): View
    {
        return new View($view['schemaname'] . '.' . $view['viewname'], $view['definition']);
    }
}