<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

interface DatabaseConfigInterface
{
    /**
     * initialize the connection data to use the service
     * */
    public static function run(array $connections): object;

    /**
     * Changes the data of the current connection with those of the
     * specified connection
     * */
    public static function connection(string $connectionName): object;

    /**
     * Activate the configuration to execute a transaction type
     * process in the service
     * */
    public static function transaction(bool $isTransaction = true): object;

    /**
     * Activate the configuration to run a process at the Schema
     * level in the service
     * */
    public static function isSchema(): object;

    /**
     * Enable the setting for nesting insert statements
     * */
    public static function enableInsert(bool $enable = false): object;
}
