<?php

declare(strict_types=1);

namespace Tests\Provider;

use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\ReadDatabaseDataInterface;
use Lion\Database\Interface\ExecuteInterface;
use Lion\Database\Interface\SchemaDriverInterface;
use Lion\Database\Interface\TransactionInterface;
use stdClass;

class CustomClassProvider implements
    DatabaseConfigInterface,
    TransactionInterface,
    SchemaDriverInterface,
    ExecuteInterface,
    ReadDatabaseDataInterface
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
    public static function run(array $connections): DatabaseConfigInterface
    {
        self::$connections = $connections;

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function connection(string $connectionName): DatabaseConfigInterface
    {
        self::$activeConnection = $connectionName;

        self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function transaction(bool $isTransaction = true): TransactionInterface
    {
        self::$isTransaction = $isTransaction;

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function isSchema(): SchemaDriverInterface
    {
        self::$isSchema = true;

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function enableInsert(bool $enable = false): SchemaDriverInterface
    {
        self::$enableInsert = $enable;

        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public static function execute(): stdClass
    {
        return (object) [
            'code' => 200,
            'status' => 'success',
            'message' => 'Execution finished',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get(): stdClass|array|DatabaseCapsuleInterface
    {
        return (object) [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getAll(): stdClass|array
    {
        return [
            (object) []
        ];
    }
}
