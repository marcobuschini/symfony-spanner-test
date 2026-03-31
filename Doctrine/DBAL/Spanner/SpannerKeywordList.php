<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Spanner;

use Doctrine\DBAL\Platforms\Keywords\KeywordList as AbstractKeywordList;

class SpannerKeywordList extends AbstractKeywordList
{
    private $keywords = [
        'ALL',
        'AND',
        'ANY',
        'ARRAY',
        'AS',
        'ASC',
        'ASSERT_ROWS_MODIFIED',
        'AT',
        'BETWEEN',
        'BY',
        'CASE',
        'CAST',
        'COLLATE',
        'CONTAINS',
        'CREATE',
        'CROSS',
        'CUBE',
        'CURRENT',
        'DEFAULT',
        'DEFINE',
        'DESC',
        'DISTINCT',
        'ELSE',
        'END',
        'ENUM',
        'ESCAPE',
        'EXCEPT',
        'EXCLUDE',
        'EXISTS',
        'EXTRACT',
        'FALSE',
        'FETCH',
        'FOLLOWING',
        'FOR',
        'FROM',
        'FULL',
        'GRAPH_TABLE',
        'GROUP',
        'GROUPING',
        'GROUPS',
        'HASH',
        'HAVING',
        'IF',
        'IGNORE',
        'IN',
        'INNER',
        'INTERSECT',
        'INTERVAL',
        'INTO',
        'IS',
        'JOIN',
        'LATERAL',
        'LEFT',
        'LIKE',
        'LIMIT',
        'LOOKUP',
        'MERGE',
        'NATURAL',
        'NEW',
        'NO',
        'NOT',
        'NULL',
        'NULLS',
        'OF',
        'ON',
        'OR',
        'ORDER',
        'OUTER',
        'OVER',
        'PARTITION',
        'PRECEDING',
        'PROTO',
        'RANGE',
        'RECURSIVE',
        'RESPECT',
        'RIGHT',
        'ROLLUP',
        'ROWS',
        'SELECT',
        'SET',
        'SOME',
        'STRUCT',
        'TABLESAMPLE',
        'THEN',
        'TO',
        'TREAT',
        'TRUE',
        'UNBOUNDED',
        'UNION',
        'UNNEST',
        'USING',
        'WHEN',
        'WHERE',
        'WINDOW',
        'WITH',
        'WITHIN'
    ];

    /**
     * Returns the list of keywords.
     *
     * @return string[]
     */
    protected function getKeywords(): array
    {
        return $this->keywords;
    }
}