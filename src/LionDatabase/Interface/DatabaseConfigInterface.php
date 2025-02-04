<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

use InvalidArgumentException;

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
     * @param array<string, array<string, array{
     *      type: string,
     *      host: string,
     *      port: int,
     *      dbname: string,
     *      user: string,
     *      password: string,
     *      options?: array<int, int>
     *  }>|string> $connections [List of available databases]
     *
     * @return DatabaseConfigInterface
     *
     * @throws InvalidArgumentException [If any initialization parameter is
     * invalid]
     */
    public static function run(array $connections): DatabaseConfigInterface;

    /**
     * Changes the data of the current connection with those of the specified
     * connection
     *
     * @param string $connectionName [Connection name]
     *
     * @return DatabaseConfigInterface
     *
     * @throws InvalidArgumentException [If the connection does not exist]
     */
    public static function connection(string $connectionName): DatabaseConfigInterface;
}
