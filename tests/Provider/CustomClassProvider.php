<?php

declare(strict_types=1);

namespace Tests\Provider;

use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;

class CustomClassProvider implements DatabaseConfigInterface, ReadDatabaseDataInterface, RunDatabaseProcessesInterface
{
    protected static array $connections = [];
    protected static string $activeConnection = '';
    protected static string $dbname = '';
    protected static bool $isTransaction = false;
    protected static bool $isSchema = false;
    protected static bool $enableInsert = false;

    /**
     * {@inheritdoc}
     */
    public static function run(array $connections): object
    {
        self::$connections = $connections;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function connection(string $connectionName): object
    {
        self::$activeConnection = $connectionName;
        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function transaction(bool $isTransaction = true): object
    {
        self::$isTransaction = $isTransaction;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSchema(): object
    {
        self::$isSchema = true;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function enableInsert(bool $enable = false): object
    {
        self::$enableInsert = $enable;

        return new static;
    }

    /**
     * {@inheritdoc}
     */
    public static function execute(): object
    {
        return (object) [
            'status' => 'success',
            'message' => 'Execution finished'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get(): array|object
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): array|object
    {
        return [];
    }
}
