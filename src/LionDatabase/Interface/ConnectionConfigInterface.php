<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines the connection data configuration of the databases
 *
 * @package Lion\Database\Interface
 */
interface ConnectionConfigInterface
{
    /**
     * Add a connection
     *
     * @param string $connectionName [Connection name]
     * @param array{
     *     type: string,
     *     host: string,
     *     port: int,
     *     dbname: string,
     *     user: string,
     *     password: string,
     *     options?: array<int, int>
     * } $options [Connection configuration data]
     *
     * @return void
     */
    public static function addConnection(string $connectionName, array $options): void;

    /**
     * Remove a connection
     *
     * @param string $connectionName [Connection name]
     *
     * @return void
     */
    public static function removeConnection(string $connectionName): void;

    /**
     * Gets all available connections
     *
     * @return array<string, array{
     *     type: string,
     *     host: string,
     *     port: int,
     *     dbname: string,
     *     user: string,
     *     password: string,
     *     options?: array<int, int>
     * }>
     */
    public static function getConnections(): array;
}
