<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Declare a method that nests the DATABASE statement in the current query
 *
 * @package Lion\Database\Interface\Drivers
 */
interface DatabaseInterface
{
    /**
     * Nests the DATABASE statement in the current query
     *
     * @return static
     */
    public static function database(): static;
}
