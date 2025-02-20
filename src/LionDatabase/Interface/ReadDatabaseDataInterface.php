<?php

declare(strict_types=1);

namespace Lion\Database\Interface;

use PDOException;
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
     * @return array<int|string, mixed>|DatabaseCapsuleInterface|stdClass
     *
     * @throws PDOException
     */
    public static function get(): array|DatabaseCapsuleInterface|stdClass;

    /**
     * Run and get an array of objects
     *
     * @return array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|stdClass
     *
     * @throws PDOException
     */
    public static function getAll(): array|stdClass;
}
