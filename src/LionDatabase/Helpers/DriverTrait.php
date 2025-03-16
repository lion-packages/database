<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

use Lion\Database\Driver;
use Lion\Database\Helpers\Constants\MySQLConstants;

/**
 * Defines the configuration methods to run Driver processes
 *
 * @package Lion\Database\Helpers
 */
trait DriverTrait
{
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
    protected static array $connections = [];

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
     * Open a group of statements to the current query
     *
     * @return void
     */
    protected static function openGroup(): void
    {
        self::$sql .= " (";
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
}
