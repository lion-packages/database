<?php

declare(strict_types=1);

namespace LionDatabase\Helpers;

trait DriverTrait
{
	protected static array $connections = [];
	protected static string $activeConnection = '';

	protected static bool $isSchema = false;
	protected static bool $isCreateSchema = false;
	protected static bool $isCreateTable = false;
	protected static bool $isCreateView = false;
	protected static bool $isCreateProcedure = false;
	protected static bool $isTransaction = false;

	protected static array $listSql = [];
	protected static string $actualCode = '';
	protected static string $sql = '';
	protected static string $dbname = '';
	protected static string $table = '';
	protected static string $procedure = '';
	protected static string $schemaStr = '';
	protected static string $view = '';
	protected static string $message = '';
	protected static array $dataInfo = [];
	protected static array $fetchMode = [];
	protected static string $engine = "INNODB";
	protected static string $characterSet = "UTF8";
	protected static string $collate = "UTF8_SPANISH_CI";
	protected static array $schemaOptions = [
		'columns' => [],
		'indexes' => [],
		'foreign' => [
			'index' => [],
			'constraint' => []
		]
	];

    protected static function clean(): void
    {
        self::$listSql = [];
        self::$actualCode = '';
        self::$sql = '';
        self::$table = '';
        self::$view = '';
        self::$procedure = '';
        self::$schemaStr = '';
        self::$dataInfo = [];
        self::$activeConnection = self::$connections['default'];
        self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
        self::$fetchMode = [];
        self::$engine == "INNODB";
        self::$characterSet = "UTF8";
        self::$collate = "UTF8_SPANISH_CI";
        self::$schemaOptions = ['columns' => [], 'indexes' => [], 'foreign' => ['index' => [], 'constraint' => []]];
        self::$isSchema = false;
        self::$isCreateSchema = false;
        self::$isCreateTable = false;
        self::$isCreateView = false;
        self::$isCreateProcedure = false;
        self::$isTransaction = false;
    }

    protected function addNewQueryList(array $listQuery): void
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
}
