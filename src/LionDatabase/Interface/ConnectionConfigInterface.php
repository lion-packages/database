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
     * @param array<string, string> $options [Connection configuration data]
     *
     * @return void
     */
    public static function addConnection(string $connectionName, array $options): void;

    /**
     * Gets all available connections
     *
     * @return array<string, array<string, string>>
     */
    public static function getConnections(): array;

    /**
     * Remove a connection
     *
     * @param string $connectionName [Connection name]
     *
     * @return void
     */
    public static function removeConnection(string $connectionName): void;
}
