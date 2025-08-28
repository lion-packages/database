<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines the configuration for database connections.
 */
interface ConnectionConfigInterface
{
    /**
     * Adds a new database connection.
     *
     * @param string $connectionName Name of the connection.
     * @param array{
     *     type: string,
     *     host?: string,
     *     port?: int,
     *     dbname: string,
     *     user?: string,
     *     password?: string,
     *     options?: array<int, int>
     * } $options Connection configuration data.
     *
     * @return void
     */
    public static function addConnection(string $connectionName, array $options): void;

    /**
     * Removes an existing database connection.
     *
     * @param string $connectionName Name of the connection to remove.
     *
     * @return void
     */
    public static function removeConnection(string $connectionName): void;

    /**
     * Returns all registered database connections.
     *
     * @return array<string, array{
     *     type: string,
     *     host?: string,
     *     port?: int,
     *     dbname: string,
     *     user?: string,
     *     password?: string,
     *     options?: array<int, int>
     * }>
     */
    public static function getConnections(): array;

    /**
     * Returns the name of the default database connection.
     *
     * @return string
     */
    public static function getDefaultConnectionName(): string;

    /**
     * Sets the default database connection name.
     *
     * @param string $connectionName Name of the connection to set as default.
     *
     * @return void
     */
    public static function setDefaultConnectionName(string $connectionName): void;
}
