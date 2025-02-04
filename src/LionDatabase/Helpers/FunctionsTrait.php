<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

/**
 * Integrate functions to concatenate transformed data to the SQL statement
 *
 * @package Lion\Database\Helpers
 */
trait FunctionsTrait
{
    use DriverTrait;
    use KeywordsTrait;

    /**
     * Nest rows in the current query
     *
     * @param array<int, array<int|string, mixed>> $rows [list of rows to nest
     * in query]
     * @param bool $addQuotes [Add quotes to column value]
     *
     * @return string
     */
    protected static function addCharacterBulk(array $rows, bool $addQuotes = false): string
    {
        $addValues = '';

        $size = count($rows) - 1;

        foreach ($rows as $key => $rowChild) {
            /** @var array<int, string> $values */
            $values = array_values($rowChild);

            $row = !self::$isSchema
                ? self::addCharacter($rowChild)
                : self::addColumns($values, true, $addQuotes);

            $str = "({$row})";

            $addValues .= $key === $size ? $str : "{$str}, ";
        }

        return $addValues;
    }

    /**
     * Nests columns with a value equal to that defined in the current query
     *
     * @param array<int|string, mixed> $columns [List of columns]
     *
     * @return string
     */
    protected static function addCharacterEqualTo(array $columns): string
    {
        $addValues = '';

        $index = 0;

        $size = count($columns) - 1;

        $columns = self::$isSchema && self::$enableInsert ? $columns : array_keys($columns);

        foreach ($columns as $column => $value) {
            if (self::$isSchema && self::$enableInsert) {
                $addValues .= $index === $size ? "{$column} = {$value}" : "{$column} = {$value}, ";
            } else {
                $addValues .= $index === $size ? "{$value} = ?" : "{$value} = ?, ";
            }

            $index++;
        }

        return $addValues;
    }

    /**
     * Nests associative values to the current query based on the number of
     * columns defined
     *
     * @param array<int|string, mixed> $rows [Row of columns]
     *
     * @return string
     */
    protected static function addCharacterAssoc(array $rows): string
    {
        $addValues = '';

        $size = count($rows) - 1;

        for ($i = 0; $i < count($rows); $i++) {
            $addValues .= $i === $size ? '?' : '?, ';
        }

        return $addValues;
    }

    /**
     * Adds the number of indexes as nested parameters in the current query
     *
     * @param array<int|string, mixed> $rows [Nesting row]
     *
     * @return string
     */
    protected static function addCharacter(array $rows): string
    {
        $keys = array_keys($rows);

        $addValues = '';

        $size = count($keys) - 1;

        foreach ($keys as $key) {
            $addValues .= $key === $size ? '?' : '?, ';
        }

        return $addValues;
    }

    /**
     * Nests columns in the current query separated by ","
     *
     * @param array<int, string> $columns [List of columns]
     * @param bool $spacing [Defines whether columns are separated by a space
     * between them]
     * @param bool $addQuotes [Defines whether columns have quotes]
     *
     * @return string
     */
    protected static function addColumns(array $columns, bool $spacing = true, bool $addQuotes = false): string
    {
        $stringColumns = '';

        /** @var array<int, string> $newColumns */
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
                        $stringColumns .= $key === $size
                            ? "'{$column}'"
                            : (!$spacing ? "'{$column}'," : "'{$column}', ");
                    } else {
                        $stringColumns .= $key === $size ? "{$column}" : (!$spacing ? "{$column}," : "{$column}, ");
                    }
                }
            }
        } else {
            $stringColumns = '*';
        }

        return $stringColumns;
    }
}
