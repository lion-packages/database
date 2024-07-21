<?php

declare(strict_types=1);

namespace Lion\Database;

use Exception;
use Lion\Database\Drivers\MySQL;
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
    const MYSQL = 'mysql';

    /**
     * Initialize database connections
     *
     * @param array $connections [List of defined connections]
     *
     * @return void
     *
     * @throws Exception [If database initialization is not successful]
     */
    public static function run(array $connections): void
    {
        if (empty($connections['default'])) {
            throw new Exception('no connection has been defined by default', 500);
        }

        $connection = $connections['connections'][$connections['default']];

        $type = trim(strtolower($connection['type']));

        switch ($type) {
            case self::MYSQL:
                MySQL::run($connections);

                SchemaMySQL::run($connections);

                break;

            default:
                throw new Exception('the defined driver does not exist', 500);

                break;
        }
    }
}
