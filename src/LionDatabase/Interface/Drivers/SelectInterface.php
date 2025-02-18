<?php

declare(strict_types=1);

namespace Lion\Database\Interface\Drivers;

/**
 * Implements a method to select data in SQL queries
 *
 * @package Lion\Database\Interface\Drivers
 */
interface SelectInterface
{
    /**
     * Nests the SELECT statement in the current query
     *
     * @return static
     */
    public static function select(): static;
}
