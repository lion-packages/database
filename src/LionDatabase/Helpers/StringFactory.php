<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

use Lion\Database\Driver;
use Lion\Database\Helpers\Constants\MySQLConstants;

/**
 * Modify defined sentence formats
 *
 * @packages Lion\Database\Helpers
 */
class StringFactory
{
    /**
     * [List of available dictionaries]
     *
     * @const DATABASE_KEYWORDS
     */
    private const array DATABASE_KEYWORDS = [
        Driver::MYSQL => MySQLConstants::KEYWORDS,
    ];

    /**
     * [List of words ignored from being added as string values]
     *
     * @const IGNORED_ELEMENTS
     */
    private const array IGNORED_ELEMENTS = [
        MySQLConstants::CURRENT_TIMESTAMP,
        'ON UPDATE ' . MySQLConstants::CURRENT_TIMESTAMP,
        ' ON UPDATE ' . MySQLConstants::CURRENT_TIMESTAMP,
    ];

    /**
     * [List of database connections]
     *
     * @var array{
     *     default: string,
     *     connections: array<string, array{
     *          type: string,
     *          host?: string,
     *          port?: int,
     *          dbname: string,
     *          user?: string,
     *          password?: string,
     *          options?: array<int, int>
     *     }>
     * } $connections
     */
    protected static array $connections;

    /**
     * [Current database name]
     *
     * @var string $dbname
     */
    protected static string $dbname = '';

    /**
     * [Name of the currently active connection]
     *
     * @var string $activeConnection
     */
    protected static string $activeConnection = '';

    /**
     * [Defines whether the process is a transaction]
     *
     * @var bool $isTransaction
     */
    protected static bool $isTransaction = false;

    /**
     * [Defines whether the process returns a number to count the affected rows]
     *
     * @var bool $withRowCount
     */
    protected static bool $withRowCount = false;

    /**
     * [Defines whether the process is a schema process]
     *
     * @var bool $isSchema
     */
    protected static bool $isSchema = false;

    /**
     * [Defines whether the process is a stored procedure]
     *
     * @var bool $isProcedure
     */
    protected static bool $isProcedure = false;

    /**
     * [Defines whether the values integrated into bindValue are concatenated]
     *
     * @var bool $enableInsert
     */
    protected static bool $enableInsert = false;

    /**
     * [List of statements separated by ';']
     *
     * @var array<int|string, string> $listSql
     */
    protected static array $listSql = [];

    /**
     * [Defines the current code to index values to the bindValue function]
     *
     * @var string $actualCode
     */
    protected static string $actualCode = '';

    /**
     * [Defines and is used to construct the SQL statement]
     *
     * @var string $sql
     */
    protected static string $sql = '';

    /**
     * [Defines and is used to select the current table]
     *
     * @var string $table
     */
    protected static string $table = '';

    /**
     * [Defines and is used to select the current view]
     *
     * @var string $view
     */
    protected static string $view = '';

    /**
     * [Defines the message of the current output]
     *
     * @var string $message
     */
    protected static string $message = 'Execution finished';

    /**
     * [Stores the information defined to add to the bindValue function]
     *
     * @var array<string, mixed> $dataInfo
     */
    protected static array $dataInfo = [];

    /**
     * [Defines the FETCH_MODE for the defined query]
     *
     * @var array<string, int> $fetchMode
     */
    protected static array $fetchMode = [];

    /**
     * [List of columns defined for the SQL query]
     *
     * @var array<string, array<string, array<string, mixed>>> $columns
     */
    protected static array $columns = [];

    /**
     * [Define the name of the current column to define its configuration in the
     * schema]
     *
     * @var string $actualColumn
     */
    protected static string $actualColumn = '';

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

    /**
     * Nests the defined parameters into the current query
     *
     * @param array<int, mixed> $queryList [List of defined parameters]
     *
     * @return void
     */
    protected static function addQueryList(array $queryList): void
    {
        foreach ($queryList as $query) {
            self::$sql .= $query;
        }
    }

    /**
     * Initializes the current query with the defined parameters
     *
     * @param array<int, string> $queryList [List of defined parameters]
     *
     * @return void
     */
    protected static function addNewQueryList(array $queryList): void
    {
        foreach ($queryList as $key => $query) {
            if ($key === 0) {
                self::$sql = $query;
            } else {
                self::$sql .= $query;
            }
        }
    }

    /**
     * Nest values to the current statement
     *
     * @param array<int, mixed> $rows [List of defined values]
     *
     * @return static
     */
    public static function addRows(array $rows): static
    {
        foreach ($rows as $row) {
            self::$dataInfo[self::$actualCode][] = $row;
        }

        return new static();
    }

    /**
     * Open a group of statements to the current query
     *
     * @return void
     */
    protected static function openGroup(): void
    {
        self::$sql .= " (";
    }

    /**
     * Replaces the values defined for a database schema, nesting the values in
     * the current statement
     *
     * @return static
     */
    protected static function buildTable(): static
    {
        if (!empty(self::$columns[self::$table])) {
            $strColumns = '';

            $strAlter = '';

            $strIndexes = '';

            $strForeigns = '';

            foreach (self::$columns[self::$table] as $config) {
                if (!empty($config['in']) && $config['in']) {
                    $strColumns .= str_replace('(?)', '', self::getKey(Driver::MYSQL, 'in')) . ' ';
                }

                $strColumns .= $config['column'];

                if (!$config['null'] && !$config['in']) {
                    $strColumns .= self::getKey(Driver::MYSQL, 'not-null');
                } elseif ($config['null'] && !$config['in']) {
                    $strColumns .= self::getKey(Driver::MYSQL, 'null');
                }

                if ($config['auto-increment']) {
                    $strColumns .= self::getKey(Driver::MYSQL, 'auto-increment');
                }

                if ($config['default'] && !empty($config['default-value'])) {
                    for ($i = 0; $i < count($config['default-value']); $i++) {
                        if (0 === $i) {
                            $strColumns .= self::getKey(Driver::MYSQL, 'default');
                        }

                        if (in_array($config['default-value'][$i], self::IGNORED_ELEMENTS, true)) {
                            $strColumns .= " {$config['default-value'][$i]}";
                        } else {
                            $strColumns .= " '{$config['default-value'][$i]}'";
                        }
                    }
                }

                if ($config['comment']) {
                    $strColumns .= self::getKey(Driver::MYSQL, 'comment') . " '{$config['comment-description']}'";
                }

                $strColumns .= ',';

                if (!empty($config['indexes'])) {
                    $sizeIndexes = count($config['indexes']);

                    for ($i = 0; $i < $sizeIndexes; $i++) {
                        $strAlter .= trim("{$config['indexes'][$i]},");
                    }
                }

                if (!empty($config['foreign'])) {
                    $strIndexes .= "{$config['foreign']['index']},";

                    $strForeigns .= "{$config['foreign']['constraint']},";
                }
            }

            $strParams = implode(
                ', ',
                array_filter(explode(',', trim($strColumns . $strAlter)), fn ($value) => !empty($value))
            );

            $strParamsIndex = implode(
                ', ',
                array_filter(explode(',', trim($strIndexes)), fn ($value) => !empty($value))
            );

            $strParamsConstraints = implode(
                ', ',
                array_filter(explode(',', trim($strForeigns)), fn ($value) => !empty($value))
            );

            $alter = 'ALTER TABLE ' . self::$dbname . '.' . self::$table;

            self::$sql = str_replace('--REPLACE-PARAMS--', $strParams, self::$sql);

            self::$sql = str_replace(
                '--REPLACE-INDEXES--',
                ('' === $strParamsIndex ? '' : "{$alter} {$strParamsIndex}; {$alter} {$strParamsConstraints};"),
                self::$sql
            );
        }

        return new static();
    }

    /**
     * Initialize the database engine properties to their initial state
     *
     * @return void
     */
    protected static function clean(): void
    {
        if (!empty(self::$connections['connections'][self::$connections['default']]['dbname'])) {
            self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
        } else {
            self::$dbname = '';
        }

        self::$activeConnection = self::$connections['default'];

        self::$isTransaction = false;

        self::$withRowCount = false;

        self::$isSchema = false;

        self::$isProcedure = false;

        self::$enableInsert = false;

        self::$listSql = [];

        self::$actualCode = '';

        self::$sql = '';

        self::$table = '';

        self::$view = '';

        self::$message = 'Execution finished';

        self::$dataInfo = [];

        self::$fetchMode = [];

        self::$columns = [];

        self::$actualColumn = '';
    }

    /**
     * Clears the parameters to be nested to the current statement
     *
     * @param array<int, string> $columns [List of columns]
     *
     * @return array<int, string>
     */
    protected static function cleanSettings(array $columns): array
    {
        $newColumns = [];

        foreach ($columns as $column) {
            $column = null === $column ? '' : $column;

            $column = is_string($column) ? trim($column) : $column;

            if (!empty($column)) {
                $newColumns[] = $column;
            }
        }

        return $newColumns;
    }

    /**
     * Closes a group of statements to the current query
     *
     * @return void
     */
    protected static function closeGroup(): void
    {
        self::$sql .= " )";
    }

    /**
     * Select the fetchMode
     *
     * @param int $fetchMode [Fetch mode]
     * @param mixed|null $value [Search value]
     *
     * @return static
     *
     * @link https://www.php.net/manual/es/pdostatement.fetch.php
     */
    public static function fetchMode(int $fetchMode, mixed $value = null): static
    {
        self::$fetchMode[self::$actualCode] = null === $value ? $fetchMode : [$fetchMode, $value];

        return new static();
    }

    /**
     * Get a value from a dictionary
     *
     * @param string $dictionary [Define the dictionary]
     * @param string $key [Value to look up in the dictionary]
     *
     * @return string
     */
    public static function getKey(string $dictionary, string $key): string
    {
        return self::DATABASE_KEYWORDS[$dictionary][$key] ?? '';
    }
}
