<?php

declare(strict_types=1);

namespace Lion\Database;

use InvalidArgumentException;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\Schema\MySQL as SchemaMySQL;

/**
 * Initialize base configuration for database connection
 *
 * @package Lion\Database
 */
abstract class Driver
{
    /**
     * [Defines the MySQL driver]
     *
     * @const MYSQL
     */
    public const string MYSQL = 'mysql';

    /**
     * [Defines the PostgreSQL driver]
     *
     * @const POSTGRESQL
     */
    public const string POSTGRESQL = 'postgresql';

    /**
     * Initialize database connections
     *
     * @param array{
     *      default: string,
     *      connections: array<string, array{
     *          type: string,
     *          host: string,
     *          port: int,
     *          dbname: string,
     *          user: string,
     *          password: string,
     *          options?: array<int, int>
     *      }>
     * } $connections [List of defined connections]
     *
     * @return void
     *
     * @throws InvalidArgumentException [If database initialization is not
     * successful]
     */
    public static function run(array $connections): void
    {
        if (empty($connections['default'])) {
            throw new InvalidArgumentException('No connection has been defined by default', 500);
        }

        $type = $connections['connections'][$connections['default']]['type'];

        switch ($type) {
            case self::MYSQL:
                MySQL::run($connections);
                SchemaMySQL::run($connections);
                break;

            case self::POSTGRESQL:
                PostgreSQL::run($connections);
                break;

            default:
                throw new InvalidArgumentException('The defined driver does not exist', 500);
        }
    }
}
