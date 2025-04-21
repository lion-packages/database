<?php

declare(strict_types=1);

namespace Tests\Provider;

use Faker\Factory;
use Lion\Database\Interface\DatabaseCapsuleInterface;

trait SQLiteProviderTrait
{
    private const QUERY_SQL_DROP_TABLE_ROLES = <<<SQL
        DROP TABLE IF EXISTS roles;
    SQL;
    private const QUERY_SQL_DROP_TABLE_USERS = <<<SQL
        DROP TABLE IF EXISTS users;
    SQL;
    private const QUERY_SQL_TABLE_ROLES = <<<SQL
        CREATE TABLE roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            roles_name TEXT NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    SQL;
    private const QUERY_SQL_TABLE_USERS = <<<SQL
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            first_name TEXT,
            last_name TEXT,
            date_of_birth TEXT, -- Almacenado en formato YYYY-MM-DD
            role_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
        );
    SQL;
    private const QUERY_SQL_INSERT_ROLES = <<<SQL
        INSERT INTO roles (roles_name) VALUES ('Admin'), ('User'), ('Editor'), ('Moderator');
    SQL;
    private const QUERY_SQL_INSERT_USERS = <<<SQL
        INSERT INTO users (username, email, password_hash, first_name, last_name, date_of_birth, role_id) VALUES
            ('john_doe', 'john.doe@example.com', 'hashed_password1', 'John', 'Doe', '1990-01-15', 1),
            ('jane_smith', 'jane.smith@example.com', 'hashed_password2', 'Jane', 'Smith', '1985-05-22', 2),
            ('alice_jones', 'alice.jones@example.com', 'hashed_password3', 'Alice', 'Jones', '1992-12-01', 3),
            ('bob_brown', 'bob.brown@example.com', 'hashed_password4', 'Bob', 'Brown', '1988-07-30', 4);
    SQL;
    private const string QUERY_SQL_NESTED_INSERT_ROLES = <<<SQL
        INSERT INTO roles (roles_name) VALUES (?);
    SQL;
    private const string QUERY_SQL_INSERT_ROLES_WITH_PARAMS = <<<SQL
        INSERT INTO roles (roles_name) VALUES ('Example');
    SQL;
    private const string QUERY_SQL_SELECT_ROLES = <<<SQL
        SELECT * FROM roles;
    SQL;
    private const string QUERY_SQL_SELECT_USERS = <<<SQL
        SELECT * FROM users;
    SQL;
    private const QUERY_SQL_SELECT_ROLES_BY_ID = <<<SQL
        SELECT * FROM roles WHERE id = 1;
    SQL;
    private const QUERY_SQL_SELECT_USERS_BY_ID = <<<SQL
        SELECT * FROM users WHERE id = 1;
    SQL;
    private const string QUERY_SQL_NESTED_SELECT_ROLES_BY_ID = <<<SQL
        SELECT * FROM roles WHERE id = ?;
    SQL;
    private const string QUERY_SQL_NESTED_SELECT_USERS_BY_ID = <<<SQL
        SELECT * FROM users WHERE id = ?;
    SQL;
    private const string QUERY_SQL_NESTED_SELECT_ROLES_BY_MULTIPLE_ID = <<<SQL
        SELECT * FROM roles WHERE id IN(?, ?);
    SQL;
    private const string QUERY_SQL_NESTED_SELECT_USERS_BY_MULTIPLE_ID = <<<SQL
        SELECT * FROM users WHERE id IN(?, ?);
    SQL;
    private const string QUERY_SQL_INSERT_ROLES_ERR = <<<SQL
        INSERT INTO roles (roles_name) VALUES (null), ('Admin');
    SQL;
    private const string QUERY_SQL_INSERT_USERS_ERR = <<<SQL
        INSERT INTO users (username, email, password_hash, first_name, last_name, date_of_birth, role_id) VALUES
            (null, 'john.doe@example.com', 'hashed_password1', 'John', 'Doe', '1990-01-15', 1),
            ('jane_smith', 'jane.smith@example.com', 'hashed_password2', 'Jane', 'Smith', '1985-05-22', 2);
    SQL;

    /**
     * @return array<int, array{
     *     connections: array<string, array<int, int>|string|null>
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
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string
     * }>
     */
    public static function getProvider(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
                'selectSql' => self::QUERY_SQL_SELECT_ROLES_BY_ID,
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
                'selectSql' => self::QUERY_SQL_SELECT_USERS_BY_ID,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string,
     *     capsule: DatabaseCapsuleInterface|IdInterface
     * }>
     */
    public static function getProviderWithFetchClass(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
                'selectSql' => self::QUERY_SQL_NESTED_SELECT_ROLES_BY_ID,
                'capsule' => new class () implements DatabaseCapsuleInterface, IdInterface {
                    private int $id;

                    private string $roles_name;

                    private string $created_at;

                    private string $updated_at;

                    /**
                     * {@inheritdoc}
                     */
                    public static function getTableName(): string
                    {
                        return 'roles';
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function getId(): int
                    {
                        return $this->id;
                    }
                },
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
                'selectSql' => self::QUERY_SQL_NESTED_SELECT_USERS_BY_ID,
                'capsule' => new class () implements DatabaseCapsuleInterface, IdInterface {
                    private int $id;

                    private string $username;

                    private string $email;

                    private string $password_hash;

                    private string $first_name;

                    private string $last_name;

                    private string $date_of_birth;

                    private string $role_id;

                    private string $created_at;

                    private string $updated_at;

                    /**
                     * {@inheritdoc}
                     */
                    public static function getTableName(): string
                    {
                        return 'users';
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function getId(): int
                    {
                        return $this->id;
                    }
                },
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string
     * }>
     */
    public static function getAllProvider(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
                'selectSql' => self::QUERY_SQL_SELECT_ROLES,
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
                'selectSql' => self::QUERY_SQL_SELECT_USERS,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string,
     *     capsule: DatabaseCapsuleInterface|IdInterface
     * }>
     */
    public static function getAllProviderWithFetchClass(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
                'selectSql' => self::QUERY_SQL_SELECT_ROLES,
                'capsule' => new class () implements DatabaseCapsuleInterface, IdInterface {
                    private int $id;

                    private string $roles_name;

                    private string $created_at;

                    private string $updated_at;

                    /**
                     * {@inheritdoc}
                     */
                    public static function getTableName(): string
                    {
                        return 'roles';
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function getId(): int
                    {
                        return $this->id;
                    }
                },
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
                'selectSql' => self::QUERY_SQL_SELECT_USERS,
                'capsule' => new class () implements DatabaseCapsuleInterface, IdInterface {
                    private int $id;

                    private string $username;

                    private string $email;

                    private string $password_hash;

                    private string $first_name;

                    private string $last_name;

                    private string $date_of_birth;

                    private string $role_id;

                    private string $created_at;

                    private string $updated_at;

                    /**
                     * {@inheritdoc}
                     */
                    public static function getTableName(): string
                    {
                        return 'users';
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function getId(): int
                    {
                        return $this->id;
                    }
                },
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string,
     *     capsule: DatabaseCapsuleInterface|IdInterface
     * }>
     */
    public static function getAllProviderWithFetchClassAndNotDataAvailable(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
                'selectSql' => self::QUERY_SQL_NESTED_SELECT_ROLES_BY_MULTIPLE_ID,
                'capsule' => new class () implements DatabaseCapsuleInterface, IdInterface {
                    private int $id;

                    private string $roles_name;

                    private string $created_at;

                    private string $updated_at;

                    /**
                     * {@inheritdoc}
                     */
                    public static function getTableName(): string
                    {
                        return 'roles';
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function getId(): int
                    {
                        return $this->id;
                    }
                },
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
                'selectSql' => self::QUERY_SQL_NESTED_SELECT_USERS_BY_MULTIPLE_ID,
                'capsule' => new class () implements DatabaseCapsuleInterface, IdInterface {
                    private int $id;

                    private string $username;

                    private string $email;

                    private string $password_hash;

                    private string $first_name;

                    private string $last_name;

                    private string $date_of_birth;

                    private string $role_id;

                    private string $created_at;

                    private string $updated_at;

                    /**
                     * {@inheritdoc}
                     */
                    public static function getTableName(): string
                    {
                        return 'users';
                    }

                    /**
                     * {@inheritdoc}
                     */
                    public function getId(): int
                    {
                        return $this->id;
                    }
                },
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string
     * }>
     */
    public static function executeInterfaceProvider(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string
     * }>
     */
    public static function transactionInterfaceProvider(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES,
                'selectSql' => self::QUERY_SQL_SELECT_ROLES,
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS,
                'selectSql' => self::QUERY_SQL_SELECT_USERS,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     dropSql: string,
     *     tableSql: string,
     *     insertSql: string,
     *     selectSql: string
     * }>
     */
    public static function transactionInterfaceWithRollbackProvider(): array
    {
        return [
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_ROLES,
                'tableSql' => self::QUERY_SQL_TABLE_ROLES,
                'insertSql' => self::QUERY_SQL_INSERT_ROLES_ERR,
                'selectSql' => self::QUERY_SQL_SELECT_ROLES,
            ],
            [
                'dropSql' => self::QUERY_SQL_DROP_TABLE_USERS,
                'tableSql' => self::QUERY_SQL_TABLE_USERS,
                'insertSql' => self::QUERY_SQL_INSERT_USERS_ERR,
                'selectSql' => self::QUERY_SQL_SELECT_USERS,
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
                'return' => <<<SQL
                INSERT INTO tasks (tasks_title, tasks_description, tasks_created_at) VALUES (?, ?, ?)
                SQL,
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
                'return' => <<<SQL
                UPDATE tasks SET tasks_title = ?, tasks_description = ?, tasks_created_at = ?
                SQL,
            ],
        ];
    }
}
