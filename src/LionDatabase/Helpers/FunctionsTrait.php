<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

trait FunctionsTrait
{
    use DriverTrait;
    use KeywordsTrait;

    protected static function addCharacterBulk(array $rows, bool $addQuotes = false): string
    {
        $addValues = '';
        $size = count($rows) - 1;

        foreach ($rows as $key => $rowChild) {
            $row = !self::$isSchema
                ? self::addCharacter($rowChild)
                : self::addColumns(array_values($rowChild), true, $addQuotes);

            $str = "({$row})";
            $addValues.= $key === $size ? $str : "{$str}, ";
        }

        return $addValues;
    }

    protected static function addCharacterEqualTo(array $columns): string
    {
        $addValues = '';
        $index = 0;
        $size = count($columns) - 1;
        $columns = self::$isSchema && self::$enableInsert ? $columns : array_keys($columns);

        foreach ($columns as $column => $value) {
            if (self::$isSchema && self::$enableInsert) {
                $addValues.= $index === $size ? "{$column} = {$value}" : "{$column} = {$value}, ";
            } else {
                $addValues.= $index === $size ? "{$value} = ?" : "{$value} = ?, ";
            }

            $index++;
        }

        return $addValues;
    }

    protected static function addCharacterAssoc(array $rows): string
    {
        $addValues = '';
        $size = count($rows) - 1;

        for ($i = 0; $i < count($rows); $i++) {
            $addValues.= $i === $size ? '?' : '?, ';
        }

        return $addValues;
    }

    protected static function addCharacter(array $rows): string
    {
        $keys = array_keys($rows);
        $addValues = '';
        $size = count($keys) - 1;

        foreach ($keys as $key) {
            $addValues.= $key === $size ? '?' : '?, ';
        }

        return $addValues;
    }

    protected static function addColumns(array $columns, bool $spacing = true, bool $addQuotes = false): string
    {
        $stringColumns = '';
        $newColumns = [];

        foreach ($columns as $column) {
            if (!empty($column)) {
                $newColumns[] = $column;
            }
        }

        $countColumns = count($newColumns);

        if ($countColumns > 0) {
            $size = $countColumns - 1;

            foreach ($newColumns as $key => $column) {
                if (!empty($column)) {
                    if (self::$isSchema && self::$enableInsert && $addQuotes) {
                        $stringColumns.= $key === $size
                            ? "'{$column}'"
                            : (!$spacing ? "'{$column}'," : "'{$column}', ");
                    } else {
                        $stringColumns.= $key === $size ? "{$column}" : (!$spacing ? "{$column}," : "{$column}, ");
                    }
                }
            }
        } else {
            $stringColumns = '*';
        }

        return $stringColumns;
    }

    protected static function addEnumColumns(array $columns, bool $spacing = true): string
    {
        $stringColumns = '';
        $newColumns = self::cleanSettings($columns);
        $countColumns = count($newColumns);
        $size = $countColumns - 1;

        if ($countColumns > 0) {
            foreach ($newColumns as $key => $column) {
                if (!empty($column)) {
                    $stringColumns.= $key === $size ? "'{$column}'" : (!$spacing ? "'{$column}'," : "'{$column}', ");
                }
            }
        } else {
            $stringColumns = '*';
        }

        return $stringColumns;
    }
}
