<?php

declare(strict_types=1);

namespace Lion\Database;

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
     * Defines the MySQL driver
     *
     * @const MYSQL
     */
    const MYSQL = 'MYSQL';

    public static function run(array $connections): object
    {
        if (empty($connections['default'])) {
            return (object) ['status' => 'database-error', 'message' => 'the default driver is required'];
        }

        $connection = $connections['connections'][$connections['default']];
        $type = trim(strtolower($connection['type']));

        switch ($type) {
            case 'mysql':
                MySQL::run($connections);
                SchemaMySQL::run($connections);
                break;

            default:
                return (object) ['status' => 'database-error', 'message' => 'the driver does not exist'];
                break;
        }

        return (object) ['status' => 'success', 'message' => 'enabled connections'];
    }
}
