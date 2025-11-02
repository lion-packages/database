<?php

declare(strict_types=1);

namespace Tests\Provider;

use Faker\Factory;

trait MySQLProviderTrait
{
    /**
     * @return array<int, array{
     *     query: string
     * }>
     */
    public static function getQueryStringProvider(): array
    {
        return [
            [
                'query' => 'INSERT INTO users (users_name, users_last_name) VALUES (?, ?)',
            ],
            [
                'query' => 'SELECT * FROM USERS',
            ],
            [
                'query' => 'INSERT INTO users (users_name, users_last_name) VALUES (?, ?);SELECT * FROM USERS',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     connections: array{
     *         default: string,
     *         connections?: array<int, int>|null
     *     }
     * }>
     */
    public static function runInterfaceWithoutConnectionsProvider(): array
    {
        return [
            [
                'connections' => [
                    'default' => 'lion_database',
                ],
            ],
            [
                'connections' => [
                    'default' => 'lion_database',
                    'connections' => null,
                ],
            ],
            [
                'connections' => [
                    'default' => 'lion_database',
                    'connections' => [],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     isTransaction: bool
     * }>
     */
    public static function transactionProvider(): array
    {
        return [
            [
                'isTransaction' => true,
            ],
            [
                'isTransaction' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     enable: bool
     * }>
     */
    public static function enableInsertProvider(): array
    {
        return [
            [
                'enable' => true,
            ],
            [
                'enable' => false,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     close: string
     * }>
     */
    public static function closeQueryProvider(): array
    {
        return [
            [
                'close' => ';',
            ],
            [
                'close' => ',',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: bool,
     *     withDatabase: bool,
     *     return: string
     * }>
     */
    public static function tableProvider(): array
    {
        return [
            [
                'table' => true,
                'withDatabase' => true,
                'return' => 'TABLE',
            ],
            [
                'table' => false,
                'withDatabase' => true,
                'return' => '',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     withDatabase: bool,
     *     return: string
     * }>
     */
    public static function tableIsStringProvider(): array
    {
        return [
            [
                'table' => 'users',
                'withDatabase' => true,
                'return' => 'lion_database.users',
            ],
            [
                'table' => 'users',
                'withDatabase' => false,
                'return' => 'users',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     view: bool,
     *     withDatabase: bool,
     *     return: string
     * }>
     */
    public static function viewProvider(): array
    {
        return [
            [
                'view' => true,
                'withDatabase' => true,
                'return' => 'VIEW',
            ],
            [
                'view' => false,
                'withDatabase' => true,
                'return' => '',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     view: string,
     *     withDatabase: bool,
     *     return: string
     * }>
     */
    public static function viewIsStringProvider(): array
    {
        return [
            [
                'view' => 'read_users',
                'withDatabase' => true,
                'return' => 'lion_database.read_users',
            ],
            [
                'view' => 'read_users',
                'withDatabase' => false,
                'return' => 'read_users',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     increase: int
     * }>
     */
    public static function offsetProvider(): array
    {
        return [
            [
                'increase' => 0,
            ],
            [
                'increase' => 10,
            ],
            [
                'increase' => 5,
            ],
            [
                'increase' => 100,
            ],
            [
                'increase' => 1000,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     elements: array<int, string>,
     *     return: string
     * }>
     */
    public static function concatProvider(): array
    {
        return [
            [
                'elements' => [
                    'name',
                    '" "',
                    'last_name',
                ],
                'return' => ' CONCAT(name, " ", last_name)',
            ],
            [
                'elements' => [
                    'idusers',
                    '" "',
                    'last_name, name',
                ],
                'return' => ' CONCAT(idusers, " ", last_name, name)',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     callableFunction: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function fromWithFunctionsProvider(): array
    {
        return [
            [
                'callableFunction' => 'table',
                'value' => 'users',
                'return' => 'FROM users'
            ],
            [
                'callableFunction' => 'view',
                'value' => 'read_users',
                'return' => 'FROM read_users'
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     query: string
     * }>
     */
    public static function queryProvider(): array
    {
        return [
            [
                'query' => 'INSERT INTO users (users_name, users_last_name) VALUES (?, ?)',
            ],
            [
                'query' => 'SELECT * FROM users',
            ],
            [
                'query' => 'UPDATE users SET users_name=?, users_last_name=? WHERE idusers=?',
            ],
            [
                'query' => 'DELETE FROM users WHERE idusers=?',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     enable: bool,
     *     table: string,
     *     columns: array<int, string>,
     *     rows: array<int, array<int, string>>,
     *     return: string
     * }>
     */
    public static function bulkProvider(): array
    {
        $faker = Factory::create();

        return [
            [
                'enable' => false,
                'table' => 'users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                ],
                'rows' => [
                    [
                        $faker->name(),
                        $faker->lastName(),
                    ],
                    [
                        $faker->name(),
                        $faker->lastName(),
                    ],
                    [
                        $faker->name(),
                        $faker->lastName(),
                    ],
                ],
                'return' => <<<SQL
                INSERT INTO users (users_name, users_last_name) VALUES (?, ?), (?, ?), (?, ?)
                SQL,
            ],
            [
                'enable' => true,
                'table' => 'users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                ],
                'rows' => [
                    [
                        'lion #1',
                        'database',
                    ],
                    [
                        'lion #2',
                        'database',
                    ],
                    [
                        'lion #3',
                        'database',
                    ],
                ],
                'return' => "INSERT INTO users (users_name, users_last_name) VALUES ('lion #1', 'database'), ('lion #2', 'database'), ('lion #3', 'database')", /** phpcs:ignore Generic.Files.LineLength */
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     return: string
     * }>
     */
    public static function deleteProvider(): array
    {
        return [
            [
                'table' => 'users',
                'return' => 'DELETE FROM users',
            ],
            [
                'table' => 'roles',
                'return' => 'DELETE FROM roles',
            ],
            [
                'table' => 'tasks',
                'return' => 'DELETE FROM tasks',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     params: array<string, string>,
     *     return: string
     * }>
     */
    public static function updateProvider(): array
    {
        $faker = Factory::create();

        return [
            [
                'table' => 'users',
                'params' => [
                    'users_name' => $faker->name(),
                    'users_last_name' => $faker->lastName(),
                ],
                'return' => 'UPDATE users SET users_name = ?, users_last_name = ?',
            ],
            [
                'table' => 'roles',
                'params' => [
                    'roles_name' => $faker->jobTitle(),
                ],
                'return' => 'UPDATE roles SET roles_name = ?',
            ],
            [
                'table' => 'tasks',
                'params' => [
                    'tasks_title' => $faker->company(),
                    'tasks_description' => $faker->companySuffix(),
                    'tasks_created_at' => $faker->date('Y-m-d H:i:s'),
                ],
                'return' => 'UPDATE tasks SET tasks_title = ?, tasks_description = ?, tasks_created_at = ?',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     params: array<string, string>,
     *     return: string
     * }>
     */
    public static function insertProvider(): array
    {
        $faker = Factory::create();

        return [
            [
                'table' => 'users',
                'params' => [
                    'users_name' => $faker->name(),
                    'users_last_name' => $faker->lastName(),
                ],
                'return' => 'INSERT INTO users (users_name, users_last_name) VALUES (?, ?)',
            ],
            [
                'table' => 'roles',
                'params' => [
                    'roles_name' => $faker->jobTitle(),
                ],
                'return' => 'INSERT INTO roles (roles_name) VALUES (?)',
            ],
            [
                'table' => 'tasks',
                'params' => [
                    'tasks_title' => $faker->company(),
                    'tasks_description' => $faker->companySuffix(),
                    'tasks_created_at' => $faker->date('Y-m-d H:i:s'),
                ],
                'return' => 'INSERT INTO tasks (tasks_title, tasks_description, tasks_created_at) VALUES (?, ?, ?)',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     function: string,
     *     value: string,
     *     columns: array<int, string>,
     *     return: string
     * }>
     */
    public static function selectProvider(): array
    {
        return [
            [
                'function' => 'table',
                'value' => 'users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'SELECT users_name, users_last_name, users_email FROM users',
            ],
            [
                'function' => 'view',
                'value' => 'read_users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'SELECT users_name, users_last_name, users_email FROM read_users',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     function: string,
     *     value: string,
     *     columns: array<int, string>,
     *     return: string
     * }>
     */
    public static function selectMultipleProvider(): array
    {
        return [
            [
                'function' => 'table',
                'value' => 'users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'SELECT users_name, users_last_name, users_email FROM users; SELECT users_name, users_last_name, users_email FROM users', // phpcs:ignore
            ],
            [
                'function' => 'view',
                'value' => 'read_users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'SELECT users_name, users_last_name, users_email FROM read_users; SELECT users_name, users_last_name, users_email FROM read_users', // phpcs:ignore
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     function: string,
     *     value: string,
     *     columns: array<int, string>,
     *     return: string
     * }>
     */
    public static function selectDistinctProvider(): array
    {
        return [
            [
                'function' => 'table',
                'value' => 'users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'SELECT DISTINCT users_name, users_last_name, users_email FROM users',
            ],
            [
                'function' => 'view',
                'value' => 'read_users',
                'columns' => [
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'SELECT DISTINCT users_name, users_last_name, users_email FROM read_users',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     like: string
     * }>
     */
    public static function likeProvider(): array
    {
        return [
            [
                'like' => '%root',
            ],
            [
                'like' => 'root%',
            ],
            [
                'like' => '%root%',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     groupBy: array<int, string>,
     *     return: string
     * }>
     */
    public static function groupByProvider(): array
    {
        return [
            [
                'groupBy' => [
                    'idusers',
                ],
                'return' => 'GROUP BY idusers',
            ],
            [
                'groupBy' => [
                    'idusers',
                    'users_name',
                ],
                'return' => 'GROUP BY idusers, users_name',
            ],
            [
                'groupBy' => [
                    'idusers',
                    'users_name',
                    'users_last_name',
                ],
                'return' => 'GROUP BY idusers, users_name, users_last_name',
            ],
            [
                'groupBy' => [
                    'idusers',
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'GROUP BY idusers, users_name, users_last_name, users_email',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     start: int,
     *     limit: int|null,
     *     add: array<int, int>,
     *     return: string
     * }>
     */
    public static function limitProvider(): array
    {
        return [
            [
                'start' => 1,
                'limit' => null,
                'add' => [
                    1,
                ],
                'return' => 'LIMIT ?',
            ],
            [
                'start' => 1,
                'limit' => 10,
                'add' => [
                    1,
                    10,
                ],
                'return' => 'LIMIT ?, ?',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     orderBy: array<int, string>,
     *     return: string
     * }>
     */
    public static function orderByProvider(): array
    {
        return [
            [
                'orderBy' => [
                    'idusers',
                ],
                'return' => 'ORDER BY idusers',
            ],
            [
                'orderBy' => [
                    'idusers',
                    'users_name',
                ],
                'return' => 'ORDER BY idusers, users_name',
            ],
            [
                'orderBy' => [
                    'idusers',
                    'users_name',
                    'users_last_name',
                ],
                'return' => 'ORDER BY idusers, users_name, users_last_name',
            ],
            [
                'orderBy' => [
                    'idusers',
                    'users_name',
                    'users_last_name',
                    'users_email',
                ],
                'return' => 'ORDER BY idusers, users_name, users_last_name, users_email',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     valueFrom: string,
     *     valueUpTo: string,
     *     withAlias: bool,
     *     return: string
     * }>
     */
    public static function joinProvider(): array
    {
        return [
            [
                'table' => 'users',
                'valueFrom' => 'idusers',
                'valueUpTo' => 'idusers',
                'return' => 'JOIN users ON idusers = idusers',
            ],
            [
                'table' => 'roles',
                'valueFrom' => 'idroles',
                'valueUpTo' => 'idroles',
                'return' => 'JOIN roles ON idroles = idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     table: string,
     *     return: string
     * }>
     */
    public static function getColumnProvider(): array
    {
        return [
            [
                'column' => 'users_name',
                'table' => 'users',
                'return' => 'users.users_name',
            ],
            [
                'column' => 'users_name',
                'table' => 'usr',
                'return' => 'usr.users_name',
            ],
            [
                'column' => 'users_name',
                'table' => '',
                'return' => 'users_name',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     table: string,
     *     return: string
     * }>
     */
    public static function columnProvider(): array
    {
        return [
            [
                'column' => 'users_name',
                'table' => 'users',
                'return' => 'users.users_name',
            ],
            [
                'column' => 'users_name',
                'table' => 'usr',
                'return' => 'usr.users_name',
            ],
            [
                'column' => 'users_name',
                'table' => '',
                'return' => 'users_name',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function equalToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers = _idusers',
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles = _idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function notEqualToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers <> _idusers',
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles <> _idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function greaterThanSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers > _idusers',
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles > _idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function lessThanSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers < _idusers',
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles < _idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function greaterThanOrEqualToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers >= _idusers',
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles >= _idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     value: string,
     *     return: string
     * }>
     */
    public static function lessThanOrEqualToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers <= _idusers',
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles <= _idroles',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     params: array<int, int>|null,
     *     return: string
     * }>
     */
    public static function inProvider(): array
    {
        return [
            [
                'params' => null,
                'return' => 'IN',
            ],
            [
                'params' => [1, 2, 3],
                'return' => 'IN(?, ?, ?)',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     params: array<int, int>|null,
     *     return: string
     * }>
     */
    public static function notInProvider(): array
    {
        return [
            [
                'params' => null,
                'return' => 'NOT IN',
            ],
            [
                'params' => [1, 2, 3],
                'return' => 'NOT IN(?, ?, ?)',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     length: int|string,
     *     return: string
     * }>
     */
    public static function varBinaryProvider(): array
    {
        return [
            [
                'name' => 'idusers',
                'length' => 'MAX',
                'return' => 'idusers VARBINARY(MAX)',
            ],
            [
                'name' => 'idusers',
                'length' => 11,
                'return' => 'idusers VARBINARY(11)',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     return: string
     * }>
     */
    public static function setProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'SET',
            ],
            [
                'value' => 'UTF8',
                'return' => 'SET = UTF8',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     return: string
     * }>
     */
    public static function collateProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'COLLATE',
            ],
            [
                'value' => 'UTF8_SPANISH_CI',
                'return' => 'COLLATE = UTF8_SPANISH_CI',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     return: string
     * }>
     */
    public static function engineProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'ENGINE',
            ],
            [
                'value' => 'INNODB',
                'return' => 'ENGINE = INNODB',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     value: int|string,
     *     return: string
     * }>
     */
    public static function defaultProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'DEFAULT',
            ],
            [
                'value' => 1,
                'return' => "DEFAULT 1",
            ],
            [
                'value' => '1',
                'return' => "DEFAULT '1'",
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     value: string,
     *     return: string
     * }>
     */
    public static function commentProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'COMMENT',
            ],
            [
                'value' => 'custom message',
                'return' => "COMMENT 'custom message'",
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     length: int|null,
     *     query: string
     * }>
     */
    public static function intProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'length' => null,
                'query' => 'idusers INT',
            ],
            [
                'column' => 'idusers',
                'length' => 11,
                'query' => 'idusers INT(11)',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     column: string,
     *     length: int|null,
     *     query: string
     * }>
     */
    public static function bigIntProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'length' => null,
                'query' => 'idusers BIGINT',
            ],
            [
                'column' => 'idusers',
                'length' => 11,
                'query' => 'idusers BIGINT(11)',
            ],
        ];
    }
}
