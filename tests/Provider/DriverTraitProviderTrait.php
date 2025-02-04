<?php

declare(strict_types=1);

namespace Tests\Provider;

use Lion\Database\Helpers\Constants\MySQLConstants;
use PDO;

trait DriverTraitProviderTrait
{
    /**
     * @return array<int, array{
     *     queryList: array<int, string>,
     *     return: string
     * }>
     */
    public static function addNewQueryListProvider(): array
    {
        return [
            [
                'queryList' => [' SELECT *', ' FROM', ' ', 'testing'],
                'return' => ' SELECT * FROM testing'
            ],
            [
                'queryList' => [' UPDATE', ' ', 'testing', ' SET'],
                'return' => ' UPDATE testing SET'
            ],
            [
                'queryList' => [' RECURSIVE', ' ', 'testing', ' AS'],
                'return' => ' RECURSIVE testing AS'
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     fetchMode: int,
     *     value: string|null
     * }>
     */
    public static function fetchModeProvider(): array
    {
        return [
            [
                'fetchMode' => PDO::FETCH_ASSOC,
                'value' => null,
            ],
            [
                'fetchMode' => PDO::FETCH_BOTH,
                'value' => null,
            ],
            [
                'fetchMode' => PDO::FETCH_OBJ,
                'value' => null,
            ],
            [
                'fetchMode' => PDO::FETCH_CLASS,
                'value' => 'App\\Http\\Controllers\\HomeController',
            ]
        ];
    }

    /**
     * @return array<int, array{
     *     table: string,
     *     actualColumn: string,
     *     row: array<string, array<string, array{
     *         primary: bool,
     *         auto-increment: bool,
     *         unique: bool,
     *         comment: bool,
     *         default: bool,
     *         null: bool,
     *         in: bool,
     *         type: string,
     *         column: string,
     *         foreign?: array{
     *             index: string,
     *             constraint: string
     *         },
     *         indexes?: array<int, string>,
     *         default-value?: string
     *     }>>,
     *     return: string
     * }>
     */
    public static function buildTable(): array
    {
        return [
            [
                'table' => 'users',
                'actualColumn' => 'idroles',
                'row' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => 'INT',
                            'column' => 'idroles INT',
                            'foreign' => [
                                'index' => 'ADD INDEX users_idroles_FK_idx (idroles ASC)',
                                'constraint' => 'ADD CONSTRAINT users_idroles_FK_idx FOREIGN KEY (idroles) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT', /** phpcs:ignore Generic.Files.LineLength */
                            ],
                        ],
                    ],
                ],
                'return' => "(idroles INT NOT NULL); ALTER TABLE lion_database.users ADD INDEX users_idroles_FK_idx (idroles ASC); ALTER TABLE lion_database.users ADD CONSTRAINT users_idroles_FK_idx FOREIGN KEY (idroles) REFERENCES lion_database.roles (idroles) ON DELETE RESTRICT ON UPDATE RESTRICT;", /** phpcs:ignore Generic.Files.LineLength */
            ],
            [
                'table' => 'users',
                'actualColumn' => 'idusers',
                'row' => [
                    'users' => [
                        'idusers' => [
                            'primary' => true,
                            'auto-increment' => true,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => 'INT',
                            'column' => 'idusers INT',
                            'indexes' => [
                                'PRIMARY KEY (idusers)',
                            ],
                        ],
                    ],
                ],
                'return' => '(idusers INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (idusers)); ',
            ],
            [
                'table' => 'users',
                'actualColumn' => 'users_email',
                'row' => [
                    'users' => [
                        'users_email' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => true,
                            'comment' => false,
                            'default' => false,
                            'null' => false,
                            'in' => false,
                            'type' => 'VARCHAR(255)',
                            'column' => 'users_email VARCHAR(255)',
                            'indexes' => [
                                'UNIQUE INDEX users_email_UNIQUE (users_email ASC)',
                            ],
                        ],
                    ],
                ],
                'return' => "(users_email VARCHAR(255) NOT NULL, UNIQUE INDEX users_email_UNIQUE (users_email ASC)); ",
            ],
            [
                'table' => 'users',
                'actualColumn' => 'idroles',
                'row' => [
                    'users' => [
                        'idroles' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => false,
                            'null' => true,
                            'in' => false,
                            'type' => 'INT',
                            'column' => 'idroles INT',
                        ],
                    ],
                ],
                'return' => '(idroles INT NULL); ',
            ],
            [
                'table' => 'users',
                'actualColumn' => 'users_nickname',
                'row' => [
                    'users' => [
                        'users_nickname' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => true,
                            'null' => true,
                            'in' => false,
                            'type' => 'VARCHAR(25)',
                            'column' => 'users_nickname VARCHAR(25)',
                            'default-value' => 'NONE',
                        ],
                    ],
                ],
                'return' => "(users_nickname VARCHAR(25) NULL DEFAULT 'NONE'); ",
            ],
            [
                'table' => 'users',
                'actualColumn' => 'users_create_at',
                'row' => [
                    'users' => [
                        'users_create_at' => [
                            'primary' => false,
                            'auto-increment' => false,
                            'unique' => false,
                            'comment' => false,
                            'default' => true,
                            'null' => true,
                            'in' => false,
                            'type' => 'TIMESTAMP',
                            'column' => 'users_create_at TIMESTAMP',
                            'default-value' => MySQLConstants::CURRENT_TIMESTAMP,
                        ],
                    ],
                ],
                'return' => "(users_create_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP); ",
            ],
        ];
    }
}
