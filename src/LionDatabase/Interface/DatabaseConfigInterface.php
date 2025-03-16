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
     * @param array{
     *     default: string,
     *     connections: array<string, array{
     *          type: string,
     *          host?: string,
     *          port?: int,
     *          dbname: string,
     *          user?: string,
     *          password?: string,
     *          options?: array<int, int>
     *     }>
     * } $connections [List of available databases]
     *
     * @return static
     *
     * @throws InvalidArgumentException [If any initialization parameter is
     * invalid]
     */
    public static function run(array $connections): static;

    /**
     * Changes the data of the current connection with those of the specified
     * connection
     *
     * @param string $connectionName [Connection name]
     *
     * @return static
     *
     * @throws InvalidArgumentException [If the connection does not exist]
     */
    public static function connection(string $connectionName): static;
}
