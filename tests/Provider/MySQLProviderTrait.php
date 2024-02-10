<?php

declare(strict_types=1);

namespace Tests\Provider;

use Faker\Factory;

trait MySQLProviderTrait
{
    public static function transactionProvider(): array
    {
        return [
            [
                'isTransaction' => true
            ],
            [
                'isTransaction' => false
            ]
        ];
    }

    public static function enableInsertProvider(): array
    {
        return [
            [
                'enable' => true,
            ],
            [
                'enable' => false
            ]
        ];
    }

    public static function closeQueryProvider(): array
    {
        return [
            [
                'close' => ';'
            ],
            [
                'close' => ','
            ]
        ];
    }

    public static function tableProvider(): array
    {
        return [
            [
                'table' => true,
                'withDatabase' => true,
                'return' => 'TABLE'
            ],
            [
                'table' => false,
                'withDatabase' => true,
                'return' => ''
            ]
        ];
    }

    public static function tableIsStringProvider(): array
    {
        return [
            [
                'table' => 'users',
                'withDatabase' => true,
                'return' => 'lion_database.users'
            ],
            [
                'table' => 'users',
                'withDatabase' => false,
                'return' => 'users'
            ]
        ];
    }

    public static function viewProvider(): array
    {
        return [
            [
                'view' => true,
                'withDatabase' => true,
                'return' => 'VIEW'
            ],
            [
                'view' => false,
                'withDatabase' => true,
                'return' => ''
            ]
        ];
    }

    public static function viewIsStringProvider(): array
    {
        return [
            [
                'view' => 'read_users',
                'withDatabase' => true,
                'return' => 'lion_database.read_users'
            ],
            [
                'view' => 'read_users',
                'withDatabase' => false,
                'return' => 'read_users'
            ]
        ];
    }

    public static function offsetProvider(): array
    {
        return [
            [
                'increase' => 0
            ],
            [
                'increase' => 10
            ],
            [
                'increase' => 5
            ],
            [
                'increase' => 100
            ],
            [
                'increase' => 1000
            ]
        ];
    }

    public static function asProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'as' => 'id',
                'return' => 'idusers AS id'
            ],
            [
                'column' => 'users_name',
                'as' => 'name',
                'return' => 'users_name AS name'
            ],
            [
                'column' => 'users_last_name',
                'as' => 'last_name',
                'return' => 'users_last_name AS last_name'
            ],
            [
                'column' => 'CONCAT(name, " ", last_name)',
                'as' => 'full_name',
                'return' => 'CONCAT(name, " ", last_name) AS full_name'
            ]
        ];
    }

    public static function concatProvider(): array
    {
        return [
            [
                'elements' => ['name', '" "', 'last_name'],
                'return' => ' CONCAT(name, " ", last_name)'
            ],
            [
                'elements' => ['idusers', '" "', 'last_name, name'],
                'return' => ' CONCAT(idusers, " ", last_name, name)'
            ]
        ];
    }

    public static function fromWithFunctionsProvider(): array
    {
        return [
            [
                'callableFunction' => 'table',
                'value' => 'users',
                'return' => 'FROM lion_database.users'
            ],
            [
                'callableFunction' => 'view',
                'value' => 'read_users',
                'return' => 'FROM lion_database.read_users'
            ]
        ];
    }

    public static function queryProvider(): array
    {
        return [
            [
                'query' => 'INSERT INTO users (users_name, users_last_name) VALUES (?, ?)'
            ],
            [
                'query' => 'SELECT * FROM users'
            ],
            [
                'query' => 'UPDATE users SET users_name=?, users_last_name=? WHERE idusers=?'
            ],
            [
                'query' => 'DELETE FROM users WHERE idusers=?'
            ]
        ];
    }

    public static function bulkProvider(): array
    {
        $faker = Factory::create();

        return [
            [
                'enable' => false,
                'table' => 'users',
                'columns' => ['users_name', 'users_last_name'],
                'rows' => [
                    [$faker->name(), $faker->lastName()],
                    [$faker->name(), $faker->lastName()],
                    [$faker->name(), $faker->lastName()]
                ],
                'return' => 'INSERT INTO lion_database.users (users_name, users_last_name) VALUES (?, ?), (?, ?), (?, ?)'
            ],
            [
                'enable' => true,
                'table' => 'users',
                'columns' => ['users_name', 'users_last_name'],
                'rows' => [
                    ['lion #1', 'database'],
                    ['lion #2', 'database'],
                    ['lion #3', 'database']
                ],
                'return' => "INSERT INTO lion_database.users (users_name, users_last_name) VALUES ('lion #1', 'database'), ('lion #2', 'database'), ('lion #3', 'database')"
            ]
        ];
    }

    public static function deleteProvider(): array
    {
        return [
            [
                'table' => 'users',
                'return' => 'DELETE FROM lion_database.users'
            ],
            [
                'table' => 'roles',
                'return' => 'DELETE FROM lion_database.roles'
            ],
            [
                'table' => 'tasks',
                'return' => 'DELETE FROM lion_database.tasks'
            ]
        ];
    }

    public static function updateProvider(): array
    {
        $faker = Factory::create();

        return [
            [
                'table' => 'users',
                'params' => [
                    'users_name' => $faker->name(),
                    'users_last_name' => $faker->lastName()
                ],
                'return' => 'UPDATE lion_database.users SET users_name = ?, users_last_name = ?'
            ],
            [
                'table' => 'roles',
                'params' => [
                    'roles_name' => $faker->jobTitle()
                ],
                'return' => 'UPDATE lion_database.roles SET roles_name = ?'
            ],
            [
                'table' => 'tasks',
                'params' => [
                    'tasks_title' => $faker->company(),
                    'tasks_description' => $faker->companySuffix(),
                    'tasks_created_at' => $faker->date('Y-m-d H:i:s')
                ],
                'return' => 'UPDATE lion_database.tasks SET tasks_title = ?, tasks_description = ?, tasks_created_at = ?'
            ]
        ];
    }

    public static function insertProvider(): array
    {
        $faker = Factory::create();

        return [
            [
                'table' => 'users',
                'params' => [
                    'users_name' => $faker->name(),
                    'users_last_name' => $faker->lastName()
                ],
                'return' => 'INSERT INTO lion_database.users (users_name, users_last_name) VALUES (?, ?)'
            ],
            [
                'table' => 'roles',
                'params' => [
                    'roles_name' => $faker->jobTitle()
                ],
                'return' => 'INSERT INTO lion_database.roles (roles_name) VALUES (?)'
            ],
            [
                'table' => 'tasks',
                'params' => [
                    'tasks_title' => $faker->company(),
                    'tasks_description' => $faker->companySuffix(),
                    'tasks_created_at' => $faker->date('Y-m-d H:i:s')
                ],
                'return' => 'INSERT INTO lion_database.tasks (tasks_title, tasks_description, tasks_created_at) VALUES (?, ?, ?)'
            ]
        ];
    }

    public static function havingProvider(): array
    {
        return [
            [
                'condition' => 'idusers = ?',
                'value' => 1,
                'return' => 'HAVING idusers = ?'
            ],
            [
                'condition' => 'idusers <> ?',
                'value' => 1,
                'return' => 'HAVING idusers <> ?'
            ],
            [
                'condition' => 'idusers > ?',
                'value' => 1,
                'return' => 'HAVING idusers > ?'
            ],
            [
                'condition' => 'idusers < ?',
                'value' => 1,
                'return' => 'HAVING idusers < ?'
            ]
        ];
    }

    public static function selectProvider(): array
    {
        return [
            [
                'function' => 'table',
                'value' => 'users',
                'columns' => ['users_name', 'users_last_name', 'users_email'],
                'return' => 'SELECT users_name, users_last_name, users_email FROM lion_database.users'
            ],
            [
                'function' => 'view',
                'value' => 'read_users',
                'columns' => ['users_name', 'users_last_name', 'users_email'],
                'return' => 'SELECT users_name, users_last_name, users_email FROM lion_database.read_users'
            ]
        ];
    }

    public static function selectDistinctProvider(): array
    {
        return [
            [
                'function' => 'table',
                'value' => 'users',
                'columns' => ['users_name', 'users_last_name', 'users_email'],
                'return' => 'SELECT DISTINCT users_name, users_last_name, users_email FROM lion_database.users'
            ],
            [
                'function' => 'view',
                'value' => 'read_users',
                'columns' => ['users_name', 'users_last_name', 'users_email'],
                'return' => 'SELECT DISTINCT users_name, users_last_name, users_email FROM lion_database.read_users'
            ]
        ];
    }

    public static function likeProvider(): array
    {
        return [
            [
                'like' => '%root'
            ],
            [
                'like' => 'root%'
            ],
            [
                'like' => '%root%'
            ]
        ];
    }

    public static function groupByProvider(): array
    {
        return [
            [
                'groupBy' => ['idusers'],
                'return' => 'GROUP BY idusers'
            ],
            [
                'groupBy' => ['idusers', 'users_name'],
                'return' => 'GROUP BY idusers, users_name'
            ],
            [
                'groupBy' => ['idusers', 'users_name', 'users_last_name'],
                'return' => 'GROUP BY idusers, users_name, users_last_name'
            ],
            [
                'groupBy' => ['idusers', 'users_name', 'users_last_name', 'users_email'],
                'return' => 'GROUP BY idusers, users_name, users_last_name, users_email'
            ]
        ];
    }

    public static function limitProvider(): array
    {
        return [
            [
                'start' => 1,
                'limit' => null,
                'add' => [1],
                'return' => 'LIMIT ?'
            ],
            [
                'start' => 1,
                'limit' => 10,
                'add' => [1, 10],
                'return' => 'LIMIT ?, ?'
            ]
        ];
    }

    public static function orderByProvider(): array
    {
        return [
            [
                'orderBy' => ['idusers'],
                'return' => 'ORDER BY idusers'
            ],
            [
                'orderBy' => ['idusers', 'users_name'],
                'return' => 'ORDER BY idusers, users_name'
            ],
            [
                'orderBy' => ['idusers', 'users_name', 'users_last_name'],
                'return' => 'ORDER BY idusers, users_name, users_last_name'
            ],
            [
                'orderBy' => ['idusers', 'users_name', 'users_last_name', 'users_email'],
                'return' => 'ORDER BY idusers, users_name, users_last_name, users_email'
            ]
        ];
    }

    public static function joinProvider(): array
    {
        return [
            [
                'table' => 'users',
                'valueFrom' => 'idusers',
                'valueUpTo' => 'idusers',
                'withAlias' => true,
                'return' => 'JOIN lion_database.users ON idusers = idusers'
            ],
            [
                'table' => 'users',
                'valueFrom' => 'idusers',
                'valueUpTo' => 'idusers',
                'withAlias' => false,
                'return' => 'JOIN users ON idusers = idusers'
            ]
        ];
    }

    public static function getColumnProvider(): array
    {
        return [
            [
                'column' => 'users_name',
                'table' => 'users',
                'return' => 'users.users_name'
            ],
            [
                'column' => 'users_name',
                'table' => 'usr',
                'return' => 'usr.users_name'
            ],
            [
                'column' => 'users_name',
                'table' => '',
                'return' => 'users_name'
            ]
        ];
    }

    public static function columnProvider(): array
    {
        return [
            [
                'column' => 'users_name',
                'table' => 'users',
                'return' => 'users.users_name'
            ],
            [
                'column' => 'users_name',
                'table' => 'usr',
                'return' => 'usr.users_name'
            ],
            [
                'column' => 'users_name',
                'table' => '',
                'return' => 'users_name'
            ]
        ];
    }

    public static function equalToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers = _idusers'
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles = _idroles'
            ]
        ];
    }

    public static function notEqualToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers <> _idusers'
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles <> _idroles'
            ]
        ];
    }

    public static function greaterThanSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers > _idusers'
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles > _idroles'
            ]
        ];
    }

    public static function lessThanSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers < _idusers'
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles < _idroles'
            ]
        ];
    }

    public static function greaterThanOrEqualToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers >= _idusers'
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles >= _idroles'
            ]
        ];
    }

    public static function lessThanOrEqualToSchemaProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'value' => '_idusers',
                'return' => 'idusers <= _idusers'
            ],
            [
                'column' => 'idroles',
                'value' => '_idroles',
                'return' => 'idroles <= _idroles'
            ]
        ];
    }

    public static function inProvider(): array
    {
        return [
            [
                'params' => null,
                'return' => 'IN'
            ],
            [
                'params' => [1, 2, 3],
                'return' => 'IN(?, ?, ?)'
            ]
        ];
    }

    public static function varBinaryProvider(): array
    {
        return [
            [
                'name' => 'idusers',
                'length' => 'MAX',
                'return' => 'idusers VARBINARY(MAX)'
            ],
            [
                'name' => 'idusers',
                'length' => 11,
                'return' => 'idusers VARBINARY(11)'
            ]
        ];
    }

    public static function setProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'SET'
            ],
            [
                'value' => 'UTF8',
                'return' => 'SET = UTF8'
            ]
        ];
    }

    public static function collateProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'COLLATE'
            ],
            [
                'value' => 'UTF8_SPANISH_CI',
                'return' => 'COLLATE = UTF8_SPANISH_CI'
            ]
        ];
    }

    public static function engineProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'ENGINE'
            ],
            [
                'value' => 'INNODB',
                'return' => 'ENGINE = INNODB'
            ]
        ];
    }

    public static function defaultProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'DEFAULT'
            ],
            [
                'value' => 1,
                'return' => "DEFAULT 1"
            ],
            [
                'value' => '1',
                'return' => "DEFAULT '1'"
            ]
        ];
    }

    public static function commentProvider(): array
    {
        return [
            [
                'value' => '',
                'return' => 'COMMENT'
            ],
            [
                'value' => 'custom message',
                'return' => "COMMENT 'custom message'"
            ]
        ];
    }

    public static function intProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'length' => null,
                'query' => 'idusers INT'
            ],
            [
                'column' => 'idusers',
                'length' => 11,
                'query' => 'idusers INT(11)'
            ]
        ];
    }

    public static function bigIntProvider(): array
    {
        return [
            [
                'column' => 'idusers',
                'length' => null,
                'query' => 'idusers BIGINT'
            ],
            [
                'column' => 'idusers',
                'length' => 11,
                'query' => 'idusers BIGINT(11)'
            ]
        ];
    }
}
