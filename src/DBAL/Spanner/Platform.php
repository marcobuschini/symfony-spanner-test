<?php

namespace DBAL\Spanner;

use \Doctrine\DBAL\Platforms\AbstractPlatform;

class Platform extends AbstractPlatform {

    protected function createReservedKeywordsList(): \Doctrine\DBAL\Platforms\Keywords\KeywordList {
        return null;
    }

    public function createSchemaManager(\Doctrine\DBAL\Connection $connection): \Doctrine\DBAL\Schema\AbstractSchemaManager {
        return null;
    }

    public function getAlterTableSQL(\Doctrine\DBAL\Schema\TableDiff $diff): array {
        return null;
    }

    public function getBigIntTypeDeclarationSQL(array $column): string {
        return null;
    }

    public function getBlobTypeDeclarationSQL(array $column): string {
        return null;
    }

    public function getBooleanTypeDeclarationSQL(array $column): string {
        return null;
    }
    
    public function getClobTypeDeclarationSQL(array $column): string {
        return null;
    }
    
    public function getCurrentDatabaseExpression(): string {
        return null;
    }
    
    protected function getDateArithmeticIntervalExpression(string $date, string $operator, string $interval, \Doctrine\DBAL\Platforms\DateIntervalUnit $unit): string {
        return null;
    }
    
    public function getDateDiffExpression(string $date1, string $date2): string {
        return null;
    }
    
    public function getDateTimeTypeDeclarationSQL(array $column): string {
        return null;
    }
    
    public function getDateTypeDeclarationSQL(array $column): string {
        return null;
    }
    
    public function getIntegerTypeDeclarationSQL(array $column): string {
        return null;
    }
    
    public function getListViewsSQL(string $database): string {
        return null;
    }

    public function getLocateExpression(string $string, string $substring, string|null $start = null): string {
        return null;
    }

    public function getSetTransactionIsolationSQL(\Doctrine\DBAL\TransactionIsolationLevel $level): string {
        return null;
    }

    public function getSmallIntTypeDeclarationSQL(array $column): string {
        return null;
    }

    public function getTimeTypeDeclarationSQL(array $column): string {
        return null;
    }

    public protected function initializeDoctrineTypeMappings(): void {
        return null;
    }

    public protected function _getCommonIntegerTypeDeclarationSQL(array $column): string {
        return null;
    }
}