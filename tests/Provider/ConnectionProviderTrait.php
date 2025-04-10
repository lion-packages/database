<?php

declare(strict_types=1);

namespace Tests\Provider;

use Lion\Database\Driver;
use PDO;

trait ConnectionProviderTrait
{
    /**
     * @return array<int, array{
     *     value: string,
     *     fetchMode: int
     * }>
     */
    public static function getValueTypeProvider(): array
    {
        return [
            [
                'value' => 'integer',
                'fetchMode' => PDO::PARAM_INT,
            ],
            [
                'value' => 'string',
                'fetchMode' => PDO::PARAM_STR,
            ],
            [
                'value' => 'boolean',
                'fetchMode' => PDO::PARAM_BOOL,
            ],
            [
                'value' => 'HEX',
                'fetchMode' => PDO::PARAM_LOB,
            ],
            [
                'value' => 'NULL',
                'fetchMode' => PDO::PARAM_NULL,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     code: string,
     *     query: string,
     *     values: array<int, int|string>
     * }>
     */
    public static function bindValueProvider(): array
    {
        return [
            [
                'code' => uniqid(),
                'query' => 'INSERT INTO users (idusers, users_name, users_last_name) VALUES (?, ?, ?)',
                'values' => [
                    1,
                    'lion',
                    'database',
                ],
            ],
            [
                'code' => uniqid(),
                'query' => <<<SQL
                INSERT INTO users (idusers, users_name, users_last_name, users_username) VALUES (?, ?, ?, ?),
                SQL,
                'values' => [
                    1,
                    'lion',
                    'database',
                    'root',
                ],
            ],
            [
                'code' => uniqid(),
                'query' => 'INSERT INTO users (idusers, users_name) VALUES (?, ?)',
                'values' => [
                    1,
                    'lion',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     driver: string,
     *     connectionName: string,
     *     connectionData: array{
     *         type: string,
     *         host: string,
     *         port: int,
     *         dbname: string,
     *         user: string,
     *         password: string
     *     }
     * }>
     */
    public static function getDatabaseInstanceProvider(): array
    {
        return [
            [
                'driver' => 'mysql',
                'connectionName' => DATABASE_NAME_CONNECTION,
                'connectionData' => CONNECTION_DATA_CONNECTION,
            ],
            [
                'driver' => 'pgsql',
                'connectionName' => DATABASE_NAME_THIRD_CONNECTION,
                'connectionData' => CONNECTION_DATA_THIRD_CONNECTION,
            ]
        ];
    }
}
