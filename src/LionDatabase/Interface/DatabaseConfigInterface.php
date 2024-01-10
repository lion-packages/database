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
}
