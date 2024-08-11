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
    public const MYSQL = 'mysql';

    /**
     * [Defines the PostgreSQL driver]
     *
     * @const POSTGRESQL
     */
    public const POSTGRESQL = 'postgresql';

    /**
     * Initialize database connections
     *
     * @param array $connections [List of defined connections]
     *
     * @return void
     *
     * @throws InvalidArgumentException [If database initialization is not
     * successful]
     */
    public static function run(array $connections): void
    {
        if (empty($connections['default'])) {
            throw new InvalidArgumentException('no connection has been defined by default', 500);
        }

        $type = trim(strtolower($connections['connections'][$connections['default']]['type']));

        switch ($type) {
            case self::MYSQL:
                MySQL::run($connections);

                SchemaMySQL::run($connections);
                break;
            case self::POSTGRESQL:
                PostgreSQL::run($connections);
                break;
            default:
                throw new InvalidArgumentException('the defined driver does not exist', 500);
                break;
        }
    }
}
