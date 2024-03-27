<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines settings to manage database connections
 *
 * @package Lion\Database\Interface
 */
interface DatabaseConfigInterface
{
    /**
     * initialize the connection data to use the service
     *
     * @param array $connections [List of available databases]
     *
     * @return object
     */
    public static function run(array $connections): object;

    /**
     * Changes the data of the current connection with those of the specified
     * connection
     *
     * @param string $connectionName [Connection name]
     *
     * @return object
     */
    public static function connection(string $connectionName): object;
}
