<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

use Lion\Database\Interface\DatabaseCapsuleInterface;
use stdClass;

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
     * @return stdClass|array<int|string, mixed>|DatabaseCapsuleInterface
     */
    public static function get(): stdClass|array|DatabaseCapsuleInterface;

    /**
     * Run and get an array of objects
     *
     * @return stdClass|array<stdClass|array<int|string, mixed>|DatabaseCapsuleInterface>
     */
    public static function getAll(): stdClass|array;
}
