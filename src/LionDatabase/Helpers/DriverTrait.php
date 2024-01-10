<?php

declare(strict_types=1);

namespace Lion\Database\Helpers;

use Lion\Database\Driver;

trait DriverTrait
{
	protected static array $connections = [];
    protected static string $dbname = '';
	protected static string $activeConnection = '';
	protected static bool $isTransaction = false;
    protected static bool $isSchema = false;
    protected static bool $isProcedure = false;
    protected static bool $enableInsert = false;
	protected static array $listSql = [];
	protected static string $actualCode = '';
	protected static string $sql = '';
	protected static string $table = '';
	protected static string $view = '';
	protected static string $message = 'Execution finished';
	protected static array $dataInfo = [];
	protected static array $fetchMode = [];
    protected static array $columns = [];
    protected static string $actualColumn = '';

    protected static function clean(): void
    {
        self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
        self::$activeConnection = self::$connections['default'];
        self::$isTransaction = false;
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

    protected static function addNewQueryList(array $listQuery): void
    {
        foreach ($listQuery as $key => $query) {
            if ($key === 0) {
                self::$sql = $query;
            } else {
                self::$sql .= $query;
            }
        }
    }

    protected static function addQueryList(array $listQuery): void
    {
        foreach ($listQuery as $query) {
            self::$sql .= $query;
        }
    }

	public static function addConnections(string $connectionName, array $options): void
	{
		self::$connections['connections'][$connectionName] = $options;
	}

	public static function getConnections(): array
	{
		return self::$connections;
	}

    public static function removeConnection(string $connectionName): void
    {
        unset(self::$connections['connections'][$connectionName]);
    }

	protected static function openGroup(): void
	{
		self::$sql .= " (";
	}

	protected static function closeGroup(): void
	{
		self::$sql .= " )";
	}

	public static function fetchMode(int $fetchMode, mixed $value = null): static
	{
        self::$fetchMode[self::$actualCode] = null === $value ? $fetchMode : [$fetchMode, $value];

		return new static;
	}

	public static function addRows(array $rows): void
    {
		foreach ($rows as $row) {
			self::$dataInfo[self::$actualCode][] = $row;
		}
	}

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

    protected static function buildTable(): object
    {
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

            if ($config['default']) {
                $strColumns .= self::getKey(Driver::MYSQL, 'default') . " '{$config['default-value']}'";
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
            array_filter(explode(',', trim($strColumns . $strAlter)), fn($value) => !empty($value))
        );

        $strParamsIndex = implode(
            ', ',
            array_filter(explode(',', trim($strIndexes)), fn($value) => !empty($value))
        );

        $strParamsConstraints = implode(
            ', ',
            array_filter(explode(',', trim($strForeigns)), fn($value) => !empty($value))
        );

        $alter = 'ALTER TABLE ' . self::$dbname . '.' . self::$table;

        self::$sql = str_replace('--REPLACE-PARAMS--', $strParams, self::$sql);

        self::$sql = str_replace(
            '--REPLACE-INDEXES--',
            ('' === $strParamsIndex ? '' : "{$alter} {$strParamsIndex}; {$alter} {$strParamsConstraints};"),
            self::$sql
        );

        return new static;
    }
}
