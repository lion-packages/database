<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

/**
 * Defines that the driver can make queries to databases
 *
 * @package Lion\Database\Interface
 */
interface ReadDatabaseDataInterface
{
    /**
     * Run and get an object from a row
     *
     * @return array|object
     */
    public static function get(): array|object;

    /**
     * Run and get an array of objects
     *
     * @return array|object
     */
    public static function getAll(): array|object;
}
