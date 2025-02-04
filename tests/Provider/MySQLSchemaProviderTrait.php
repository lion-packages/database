<?php

declare(strict_types=1);

namespace Tests\Provider;

trait MySQLSchemaProviderTrait
{
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
     *     database: string,
     *     query: string
     * }>
     */
    public static function createDatabaseProvider(): array
    {
        return [
            [
                'database' => 'lion_database_1',
                'query' => 'CREATE DATABASE IF NOT EXISTS `lion_database_1`',
            ],
            [
                'database' => 'lion_database_2',
                'query' => 'CREATE DATABASE IF NOT EXISTS `lion_database_2`',
            ],
            [
                'database' => 'lion_database_3',
                'query' => 'CREATE DATABASE IF NOT EXISTS `lion_database_3`',
            ],
            [
                'database' => 'lion_database_4',
                'query' => 'CREATE DATABASE IF NOT EXISTS `lion_database_4`',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     database: string,
     *     query: string,
     *     connection: array{
     *         type: string,
     *         host: string,
     *         port: int,
     *         dbname: string,
     *         user: string,
     *         password: string
     *     }
     * }>
     */
    public static function dropDatabaseProvider(): array
    {
        /** @var string $host */
        $host = DATABASE_HOST_MYSQL;

        return [
            [
                'database' => 'lion_database_1',
                'query' => 'USE `lion_database_1`; DROP DATABASE `lion_database_1`',
                'connection' => [
                    'type' => 'mysql',
                    'host' => $host,
                    'port' => 3306,
                    'dbname' => 'lion_database_1',
                    'user' => 'root',
                    'password' => 'lion',
                ],
            ],
            [
                'database' => 'lion_database_2',
                'query' => 'USE `lion_database_2`; DROP DATABASE `lion_database_2`',
                'connection' => [
                    'type' => 'mysql',
                    'host' => $host,
                    'port' => 3306,
                    'dbname' => 'lion_database_2',
                    'user' => 'root',
                    'password' => 'lion',
                ],
            ],
            [
                'database' => 'lion_database_3',
                'query' => 'USE `lion_database_3`; DROP DATABASE `lion_database_3`',
                'connection' => [
                    'type' => 'mysql',
                    'host' => $host,
                    'port' => 3306,
                    'dbname' => 'lion_database_3',
                    'user' => 'root',
                    'password' => 'lion',
                ],
            ],
            [
                'database' => 'lion_database_4',
                'query' => 'USE `lion_database_4`; DROP DATABASE `lion_database_4`',
                'connection' => [
                    'type' => 'mysql',
                    'host' => $host,
                    'port' => 3306,
                    'dbname' => 'lion_database_4',
                    'user' => 'root',
                    'password' => 'lion',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     query: string
     * }>
     */
    public static function createTableProvider(): array
    {
        return [
            [
                'table' => 'users',
                'query' => "USE `lion_database`; DROP TABLE IF EXISTS lion_database.users; CREATE TABLE lion_database.users (id INT NOT NULL AUTO_INCREMENT, num INT NOT NULL COMMENT 'comment num', idroles INT NOT NULL, PRIMARY KEY (id)) ENGINE = INNODB DEFAULT CHARACTER SET = UTF8MB4 COLLATE = UTF8MB4_SPANISH_CI; ALTER TABLE lion_database.users ADD INDEX users_idroles_FK_idx (idroles ASC); ALTER TABLE lion_database.users ADD CONSTRAINT users_idroles_FK FOREIGN KEY (idroles) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT;", /** phpcs:ignore Generic.Files.LineLength */
            ],
            [
                'table' => 'roles',
                'query' => "USE `lion_database`; DROP TABLE IF EXISTS lion_database.roles; CREATE TABLE lion_database.roles (id INT NOT NULL AUTO_INCREMENT, num INT NOT NULL COMMENT 'comment num', idroles INT NOT NULL, PRIMARY KEY (id)) ENGINE = INNODB DEFAULT CHARACTER SET = UTF8MB4 COLLATE = UTF8MB4_SPANISH_CI; ALTER TABLE lion_database.roles ADD INDEX roles_idroles_FK_idx (idroles ASC); ALTER TABLE lion_database.roles ADD CONSTRAINT roles_idroles_FK FOREIGN KEY (idroles) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT;", /** phpcs:ignore Generic.Files.LineLength */
            ],
            [
                'table' => 'tasks',
                'query' => "USE `lion_database`; DROP TABLE IF EXISTS lion_database.tasks; CREATE TABLE lion_database.tasks (id INT NOT NULL AUTO_INCREMENT, num INT NOT NULL COMMENT 'comment num', idroles INT NOT NULL, PRIMARY KEY (id)) ENGINE = INNODB DEFAULT CHARACTER SET = UTF8MB4 COLLATE = UTF8MB4_SPANISH_CI; ALTER TABLE lion_database.tasks ADD INDEX tasks_idroles_FK_idx (idroles ASC); ALTER TABLE lion_database.tasks ADD CONSTRAINT tasks_idroles_FK FOREIGN KEY (idroles) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT;", /** phpcs:ignore Generic.Files.LineLength */
            ],
            [
                'table' => 'students',
                'query' => "USE `lion_database`; DROP TABLE IF EXISTS lion_database.students; CREATE TABLE lion_database.students (id INT NOT NULL AUTO_INCREMENT, num INT NOT NULL COMMENT 'comment num', idroles INT NOT NULL, PRIMARY KEY (id)) ENGINE = INNODB DEFAULT CHARACTER SET = UTF8MB4 COLLATE = UTF8MB4_SPANISH_CI; ALTER TABLE lion_database.students ADD INDEX students_idroles_FK_idx (idroles ASC); ALTER TABLE lion_database.students ADD CONSTRAINT students_idroles_FK FOREIGN KEY (idroles) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT;", /** phpcs:ignore Generic.Files.LineLength */
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     query: string
     * }>
     */
    public static function dropTableProvider(): array
    {
        return [
            [
                'table' => 'users',
                'query' => 'USE `lion_database`; DROP TABLE lion_database.users;',
            ],
            [
                'table' => 'roles',
                'query' => 'USE `lion_database`; DROP TABLE lion_database.roles;',
            ],
            [
                'table' => 'tasks',
                'query' => 'USE `lion_database`; DROP TABLE lion_database.tasks;',
            ],
            [
                'table' => 'students',
                'query' => 'USE `lion_database`; DROP TABLE lion_database.students;',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     enableForeignKeyChecks: bool
     * }>
     */
    public static function truncateTableProvider(): array
    {
        return [
            [
                'table' => 'users',
                'enableForeignKeyChecks' => false
            ],
            [
                'table' => 'roles',
                'enableForeignKeyChecks' => false
            ],
            [
                'table' => 'tasks',
                'enableForeignKeyChecks' => true
            ],
            [
                'table' => 'students',
                'enableForeignKeyChecks' => true
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     storeProcedure: string
     * }>
     */
    public static function createStoreProcedureProvider(): array
    {
        return [
            [
                'table' => 'users',
                'storeProcedure' => 'create_users',
            ],
            [
                'table' => 'roles',
                'storeProcedure' => 'create_roles',
            ],
            [
                'table' => 'tasks',
                'storeProcedure' => 'create_tasks',
            ],
            [
                'table' => 'students',
                'storeProcedure' => 'create_students',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     storeProcedure: string
     * }>
     */
    public static function dropStoreProcedureProvider(): array
    {
        return [
            [
                'table' => 'users',
                'storeProcedure' => 'create_users',
            ],
            [
                'table' => 'roles',
                'storeProcedure' => 'create_roles',
            ],
            [
                'table' => 'tasks',
                'storeProcedure' => 'create_tasks',
            ],
            [
                'table' => 'students',
                'storeProcedure' => 'create_students',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     parentTable: string,
     *     childTable: string,
     *     view: string
     * }>
     */
    public static function createViewProvider(): array
    {
        return [
            [
                'parentTable' => 'roles',
                'childTable' => 'users',
                'view' => 'read_users',
            ],
            [
                'parentTable' => 'roles',
                'childTable' => 'tasks',
                'view' => 'read_tasks',
            ],
            [
                'parentTable' => 'tasks',
                'childTable' => 'users',
                'view' => 'read_users',
            ],
            [
                'parentTable' => 'roles',
                'childTable' => 'students',
                'view' => 'read_students',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     view: string
     * }>
     */
    public static function dropViewProvider(): array
    {
        return [
            [
                'table' => 'users',
                'view' => 'read_users',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, array<int, string>|bool>>>
     * }>
     */
    public static function primaryKeyProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => true,
                            'indexes' => [
                                ' PRIMARY KEY (idusers)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'roles',
                'column' => 'idroles',
                'configColumn' => [
                    'roles' => [
                        'idroles' => [
                            'primary' => true,
                            'indexes' => [
                                ' PRIMARY KEY (idroles)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'idtasks',
                'configColumn' => [
                    'tasks' => [
                        'idtasks' => [
                            'primary' => true,
                            'indexes' => [
                                ' PRIMARY KEY (idtasks)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'students',
                'column' => 'idstudents',
                'configColumn' => [
                    'students' => [
                        'idstudents' => [
                            'primary' => true,
                            'indexes' => [
                                ' PRIMARY KEY (idstudents)',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool>>>
     * }>
     */
    public static function autoIncrementProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'auto-increment' => true,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'roles',
                'column' => 'idroles',
                'configColumn' => [
                    'roles' => [
                        'idroles' => [
                            'auto-increment' => true,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'idtasks',
                'configColumn' => [
                    'tasks' => [
                        'idtasks' => [
                            'auto-increment' => true,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'students',
                'column' => 'idstudents',
                'configColumn' => [
                    'students' => [
                        'idstudents' => [
                            'auto-increment' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool>>>
     * }>
     */
    public static function notNullProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'null' => false,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'roles',
                'column' => 'idroles',
                'configColumn' => [
                    'roles' => [
                        'idroles' => [
                            'null' => false,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'idtasks',
                'configColumn' => [
                    'tasks' => [
                        'idtasks' => [
                            'null' => false,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'students',
                'column' => 'idstudents',
                'configColumn' => [
                    'students' => [
                        'idstudents' => [
                            'null' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool>>>
     * }>
     */
    public static function nullProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'null' => true,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'roles',
                'column' => 'idroles',
                'configColumn' => [
                    'roles' => [
                        'idroles' => [
                            'null' => true,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'idtasks',
                'configColumn' => [
                    'tasks' => [
                        'idtasks' => [
                            'null' => true,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'students',
                'column' => 'idstudents',
                'configColumn' => [
                    'students' => [
                        'idstudents' => [
                            'null' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     comment: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function commentProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'comment' => 'testing',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'comment' => true,
                            'comment-description' => 'testing',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'roles',
                'column' => 'idroles',
                'comment' => 'testing',
                'configColumn' => [
                    'roles' => [
                        'idroles' => [
                            'comment' => true,
                            'comment-description' => 'testing',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'idtasks',
                'comment' => 'testing',
                'configColumn' => [
                    'tasks' => [
                        'idtasks' => [
                            'comment' => true,
                            'comment-description' => 'testing',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'students',
                'column' => 'idstudents',
                'comment' => 'testing',
                'configColumn' => [
                    'students' => [
                        'idstudents' => [
                            'comment' => true,
                            'comment-description' => 'testing',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, array<int, string>|bool>>>
     * }>
     */
    public static function uniqueProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'unique' => true,
                            'indexes' => [
                                ' UNIQUE INDEX idusers_UNIQUE (idusers ASC)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'roles',
                'column' => 'idroles',
                'configColumn' => [
                    'roles' => [
                        'idroles' => [
                            'unique' => true,
                            'indexes' => [
                                ' UNIQUE INDEX idroles_UNIQUE (idroles ASC)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'idtasks',
                'configColumn' => [
                    'tasks' => [
                        'idtasks' => [
                            'unique' => true,
                            'indexes' => [
                                ' UNIQUE INDEX idtasks_UNIQUE (idtasks ASC)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'students',
                'column' => 'idstudents',
                'configColumn' => [
                    'students' => [
                        'idstudents' => [
                            'unique' => true,
                            'indexes' => [
                                ' UNIQUE INDEX idstudents_UNIQUE (idstudents ASC)',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     default: int|null|string,
     *     configColumn: array<string, array<string, array<string, bool|int|string|null>>>
     * }>
     */
    public static function defaultProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'default' => null,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'default' => true,
                            'default-value' => null,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idusers',
                'default' => 1,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'default' => true,
                            'default-value' => 1,
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'users_name',
                'default' => 'root',
                'configColumn' => [
                    'users' => [
                        'users_name' => [
                            'default' => true,
                            'default-value' => 'root',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     relation: array<string, string>,
     *     configColumn: array<string, array<string, array<string, array<string, string>>>>
     * }>
     */
    public static function foreignProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'relation' => [
                    'table' => 'roles',
                    'column' => 'idroles',
                ],
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'foreign' => [
                                'index' => 'ADD INDEX users_idusers_FK_idx (idusers ASC)',
                                'constraint' => 'ADD CONSTRAINT users_idusers_FK FOREIGN KEY (idusers) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT', /** phpcs:ignore Generic.Files.LineLength */
                            ],
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idtasks',
                'relation' => [
                    'table' => 'tasks',
                    'column' => 'idtasks',
                ],
                'configColumn' => [
                    'users' => [
                        'idtasks' => [
                            'foreign' => [
                                'index' => 'ADD INDEX users_idtasks_FK_idx (idtasks ASC)',
                                'constraint' => 'ADD CONSTRAINT users_idtasks_FK FOREIGN KEY (idtasks) REFERENCES lion_database.tasks (idtasks) ON DELETE RESTRICT ON UPDATE RESTRICT', /** phpcs:ignore Generic.Files.LineLength */
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int|null,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function intProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => null,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' INT',
                            'column' => 'idusers INT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => null,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' INT',
                            'column' => 'idroles INT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => 11,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' INT(11)',
                            'column' => 'idusers INT(11)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => 11,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' INT(11)',
                            'column' => 'idroles INT(11)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int|null,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function bigIntProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => null,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' BIGINT',
                            'column' => 'idusers BIGINT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => null,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' BIGINT',
                            'column' => 'idroles BIGINT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => 11,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' BIGINT(11)',
                            'column' => 'idusers BIGINT(11)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => 11,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' BIGINT(11)',
                            'column' => 'idroles BIGINT(11)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function decimalProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DECIMAL',
                            'column' => 'idusers DECIMAL',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DECIMAL',
                            'column' => 'idroles DECIMAL',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function doubleProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DOUBLE',
                            'column' => 'idusers DOUBLE',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DOUBLE',
                            'column' => 'idroles DOUBLE',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function floatProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' FLOAT',
                            'column' => 'idusers FLOAT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' FLOAT',
                            'column' => 'idroles FLOAT',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function mediumIntProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => 5,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' MEDIUMINT(5)',
                            'column' => 'idusers MEDIUMINT(5)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => 1,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' MEDIUMINT(1)',
                            'column' => 'idroles MEDIUMINT(1)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function realProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'amount',
                'configColumn' => [
                    'users' => [
                        'amount' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' REAL',
                            'column' => 'amount REAL',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'quantity',
                'configColumn' => [
                    'users' => [
                        'quantity' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' REAL',
                            'column' => 'quantity REAL',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function smallIntProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => 5,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' SMALLINT(5)',
                            'column' => 'idusers SMALLINT(5)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => 1,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' SMALLINT(1)',
                            'column' => 'idroles SMALLINT(1)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function tinyIntProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'length' => 5,
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TINYINT(5)',
                            'column' => 'idusers TINYINT(5)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'length' => 1,
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TINYINT(1)',
                            'column' => 'idroles TINYINT(1)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function blobProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'image_data',
                'configColumn' => [
                    'users' => [
                        'image_data' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' BLOB',
                            'column' => 'image_data BLOB',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'audio_data',
                'configColumn' => [
                    'users' => [
                        'audio_data' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' BLOB',
                            'column' => 'audio_data BLOB',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function varBinaryProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'image_data',
                'length' => 45,
                'configColumn' => [
                    'users' => [
                        'image_data' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' VARBINARY(45)',
                            'column' => 'image_data VARBINARY(45)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'audio_data',
                'length' => 255,
                'configColumn' => [
                    'users' => [
                        'audio_data' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' VARBINARY(255)',
                            'column' => 'audio_data VARBINARY(255)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function charProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'users_name',
                'length' => 25,
                'configColumn' => [
                    'users' => [
                        'users_name' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' CHAR(25)',
                            'column' => 'users_name CHAR(25)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'users_last_name',
                'length' => 45,
                'configColumn' => [
                    'users' => [
                        'users_last_name' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' CHAR(45)',
                            'column' => 'users_last_name CHAR(45)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function jsonProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'documents',
                'configColumn' => [
                    'users' => [
                        'documents' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' JSON',
                            'column' => 'documents JSON',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'app_settings',
                'configColumn' => [
                    'users' => [
                        'app_settings' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' JSON',
                            'column' => 'app_settings JSON',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function ncharProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'documents',
                'length' => 5,
                'configColumn' => [
                    'users' => [
                        'documents' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' NCHAR(5)',
                            'column' => 'documents NCHAR(5)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'app_settings',
                'length' => 10,
                'configColumn' => [
                    'users' => [
                        'app_settings' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' NCHAR(10)',
                            'column' => 'app_settings NCHAR(10)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function nvarcharProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'users_name',
                'length' => 25,
                'configColumn' => [
                    'users' => [
                        'users_name' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' NVARCHAR(25)',
                            'column' => 'users_name NVARCHAR(25)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'users_last_name',
                'length' => 45,
                'configColumn' => [
                    'users' => [
                        'users_last_name' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' NVARCHAR(45)',
                            'column' => 'users_last_name NVARCHAR(45)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function varcharProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'users_name',
                'length' => 25,
                'configColumn' => [
                    'users' => [
                        'users_name' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' VARCHAR(25)',
                            'column' => 'users_name VARCHAR(25)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'users_last_name',
                'length' => 45,
                'configColumn' => [
                    'users' => [
                        'users_last_name' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' VARCHAR(45)',
                            'column' => 'users_last_name VARCHAR(45)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function longTextProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'content',
                'configColumn' => [
                    'users' => [
                        'content' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' LONGTEXT',
                            'column' => 'content LONGTEXT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'summary',
                'configColumn' => [
                    'users' => [
                        'summary' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' LONGTEXT',
                            'column' => 'summary LONGTEXT',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function mediumTextProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'content',
                'configColumn' => [
                    'users' => [
                        'content' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' MEDIUMTEXT',
                            'column' => 'content MEDIUMTEXT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'summary',
                'configColumn' => [
                    'users' => [
                        'summary' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' MEDIUMTEXT',
                            'column' => 'summary MEDIUMTEXT',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     length: int,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function textProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'content',
                'length' => 5,
                'configColumn' => [
                    'users' => [
                        'content' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TEXT(5)',
                            'column' => 'content TEXT(5)',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'summary',
                'length' => 10,
                'configColumn' => [
                    'users' => [
                        'summary' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TEXT(10)',
                            'column' => 'summary TEXT(10)',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function tinyTextProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'content',
                'configColumn' => [
                    'users' => [
                        'content' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TINYTEXT',
                            'column' => 'content TINYTEXT',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'summary',
                'configColumn' => [
                    'users' => [
                        'summary' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TINYTEXT',
                            'column' => 'summary TINYTEXT',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function enumProvider(): array
    {
        return [
            [
                'table' => 'users',
                'column' => 'idusers',
                'configColumn' => [
                    'users' => [
                        'idusers' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' ENUM(\'1\', \'2\', \'3\')',
                            'column' => 'idusers ENUM(\'1\', \'2\', \'3\')',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'users',
                'column' => 'idroles',
                'configColumn' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' ENUM(\'1\', \'2\', \'3\')',
                            'column' => 'idroles ENUM(\'1\', \'2\', \'3\')',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function dateProvider(): array
    {
        return [
            [
                'table' => 'tasks',
                'column' => 'date',
                'configColumn' => [
                    'tasks' => [
                        'date' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DATE',
                            'column' => 'date DATE',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'appointment_date',
                'configColumn' => [
                    'tasks' => [
                        'appointment_date' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DATE',
                            'column' => 'appointment_date DATE',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function timeProvider(): array
    {
        return [
            [
                'table' => 'tasks',
                'column' => 'time',
                'configColumn' => [
                    'tasks' => [
                        'time' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TIME',
                            'column' => 'time TIME',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'duration',
                'configColumn' => [
                    'tasks' => [
                        'duration' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TIME',
                            'column' => 'duration TIME',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function timeStampProvider(): array
    {
        return [
            [
                'table' => 'tasks',
                'column' => 'date',
                'configColumn' => [
                    'tasks' => [
                        'date' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TIMESTAMP',
                            'column' => 'date TIMESTAMP',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'appointment_date',
                'configColumn' => [
                    'tasks' => [
                        'appointment_date' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' TIMESTAMP',
                            'column' => 'appointment_date TIMESTAMP',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     column: string,
     *     configColumn: array<string, array<string, array<string, bool|string>>>
     * }>
     */
    public static function dateTimeProvider(): array
    {
        return [
            [
                'table' => 'tasks',
                'column' => 'date',
                'configColumn' => [
                    'tasks' => [
                        'date' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DATETIME',
                            'column' => 'date DATETIME',
                        ],
                    ],
                ],
            ],
            [
                'table' => 'tasks',
                'column' => 'appointment_date',
                'configColumn' => [
                    'tasks' => [
                        'appointment_date' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => ' DATETIME',
                            'column' => 'appointment_date DATETIME',
                        ],
                    ],
                ],
            ],
        ];
    }
}
